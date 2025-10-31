<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Request;

use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Exception\ApiException;

final class CreateChatMessagesRequest extends AbstractSiliconFlowRequest
{
    /**
     * @param array<int, array<string, mixed>> $messages
     * @param array<string, mixed>             $options
     */
    public function __construct(
        SiliconFlowConfig $config,
        private readonly string $model,
        private readonly array $messages,
        private readonly int $maxTokens,
        private readonly array $options = [],
        ?int $timeout = null,
    ) {
        parent::__construct($config, $timeout ?? 30);
    }

    public function getRequestPath(): string
    {
        return '/v1/messages';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(
            [
                'model' => $this->model,
                'messages' => $this->messages,
                'max_tokens' => $this->maxTokens,
            ],
            $this->options,
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getRequestOptions(): array
    {
        return [
            'json' => $this->toArray(),
            'timeout' => $this->getTimeout(),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getApiToken(),
                'Content-Type' => 'application/json',
                'Accept' => $this->expectsStream() ? 'text/event-stream' : 'application/json',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function parseResponse(string $content): array
    {
        return $this->expectsStream()
            ? $this->decodeStreamResponse($content)
            : $this->decodeJsonResponse($content);
    }

    public function validate(): void
    {
        parent::validate();

        if ('' === trim($this->model)) {
            throw new ApiException('Chat Messages 请求必须指定模型。');
        }

        if ($this->maxTokens <= 0) {
            throw new ApiException('Chat Messages 请求必须提供大于 0 的 max_tokens。');
        }

        if ([] === $this->messages) {
            throw new ApiException('Chat Messages 请求必须包含至少一条 message。');
        }

        foreach ($this->messages as $index => $message) {
            if (!isset($message['role'], $message['content'])) {
                throw new ApiException(sprintf('Message #%d 必须包含 role 与 content。', $index));
            }
        }
    }

    public function expectsStream(): bool
    {
        return (bool) ($this->options['stream'] ?? false);
    }

    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonResponse(string $content): array
    {
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            throw new ApiException('SiliconFlow 返回了无法解析的响应：' . $content);
        }

        // Ensure we have string keys
        /** @var array<string, mixed> $validated */
        $validated = [];
        foreach ($decoded as $key => $value) {
            $validated[(string) $key] = $value;
        }

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeStreamResponse(string $content): array
    {
        $lines = $this->splitContentIntoLines($content);
        $result = $this->initializeStreamResult();
        $contentBlocks = [];

        foreach ($lines as $rawLine) {
            $decoded = $this->parseLine($rawLine);
            if (null === $decoded) {
                continue;
            }

            $result = $this->updateResultWithDecodedData($result, $decoded);
            $contentBlocks = $this->processContentChunk($decoded, $contentBlocks);
        }

        $result['content'] = $contentBlocks;
        return $result;
    }

    /**
     * @return string[]
     */
    private function splitContentIntoLines(string $content): array
    {
        $lines = preg_split('/\r?\n/', $content);
        return false === $lines ? [] : $lines;
    }

    /**
     * @return array<string, mixed>
     */
    private function initializeStreamResult(): array
    {
        return [
            'id' => null,
            'type' => 'message',
            'role' => 'assistant',
            'model' => null,
            'content' => [],
            'stop_reason' => null,
            'stop_sequence' => null,
            'usage' => null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseLine(string $rawLine): ?array
    {
        $line = trim($rawLine);
        if ('' === $line || !str_starts_with($line, 'data:')) {
            return null;
        }

        $payload = trim(substr($line, 5));
        if ('' === $payload) {
            return null;
        }

        if ('[DONE]' === $payload) {
            return null;
        }

        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            return null;
        }

        // Ensure we have string keys
        /** @var array<string, mixed> $validated */
        $validated = [];
        foreach ($decoded as $key => $value) {
            $validated[(string) $key] = $value;
        }

        return $validated;
    }

    /**
     * @param array<string, mixed> $result
     * @param array<string, mixed> $decoded
     * @return array<string, mixed>
     */
    private function updateResultWithDecodedData(array $result, array $decoded): array
    {
        $decodedMessage = isset($decoded['message']) && is_array($decoded['message']) ? $decoded['message'] : [];

        $result['id'] = $result['id'] ?? $decoded['id'] ?? ($decodedMessage['id'] ?? null);
        $result['model'] = $result['model'] ?? $decoded['model'] ?? ($decodedMessage['model'] ?? null);

        $role = $decoded['role'] ?? $decodedMessage['role'] ?? $result['role'];
        if (is_string($role)) {
            $result['role'] = $role;
        }

        if (array_key_exists('stop_reason', $decoded)) {
            $result['stop_reason'] = $decoded['stop_reason'];
        }

        if (array_key_exists('stop_sequence', $decoded)) {
            $result['stop_sequence'] = $decoded['stop_sequence'];
        }

        if (isset($decoded['usage']) && is_array($decoded['usage'])) {
            $result['usage'] = $decoded['usage'];
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $decoded
     * @param array<string, mixed>[] $contentBlocks
     * @return array<string, mixed>[]
     */
    private function processContentChunk(array $decoded, array $contentBlocks): array
    {
        $decodedDelta = isset($decoded['delta']) && is_array($decoded['delta']) ? $decoded['delta'] : [];
        $contentChunk = $decoded['content'] ?? $decodedDelta['content'] ?? [];

        if (!is_array($contentChunk)) {
            return $contentBlocks;
        }

        foreach ($contentChunk as $block) {
            if (!is_array($block) || !array_key_exists('type', $block)) {
                continue;
            }

            // Ensure we have string keys
            /** @var array<string, mixed> $validatedBlock */
            $validatedBlock = [];
            foreach ($block as $key => $value) {
                $validatedBlock[(string) $key] = $value;
            }

            $contentBlocks = $this->processBlock($validatedBlock, $contentBlocks);
        }

        return $contentBlocks;
    }

    /**
     * @param array<string, mixed> $block
     * @param array<string, mixed>[] $contentBlocks
     * @return array<string, mixed>[]
     */
    private function processBlock(array $block, array $contentBlocks): array
    {
        if ('text' !== $block['type']) {
            $contentBlocks[] = $block;
            return $contentBlocks;
        }

        return $this->processTextBlock($block, $contentBlocks);
    }

    /**
     * @param array<string, mixed> $block
     * @param array<string, mixed>[] $contentBlocks
     * @return array<string, mixed>[]
     */
    private function processTextBlock(array $block, array $contentBlocks): array
    {
        $existingIndex = array_key_last($contentBlocks);

        if (is_int($existingIndex) && $this->shouldAppendToExistingBlock($contentBlocks, $existingIndex)) {
            $contentBlocks = $this->appendToExistingBlock($contentBlocks, $existingIndex, $block);
        } else {
            $contentBlocks = $this->createNewTextBlock($contentBlocks, $block);
        }

        return $contentBlocks;
    }

    /**
     * @param array<string, mixed>[] $contentBlocks
     */
    private function shouldAppendToExistingBlock(array $contentBlocks, int $existingIndex): bool
    {
        return 'text' === ($contentBlocks[$existingIndex]['type'] ?? '');
    }

    /**
     * @param array<string, mixed>[] $contentBlocks
     * @param array<string, mixed> $block
     * @return array<string, mixed>[]
     */
    private function appendToExistingBlock(array $contentBlocks, int $existingIndex, array $block): array
    {
        $existingText = $this->extractTextFromBlock($contentBlocks[$existingIndex]);
        $newText = $this->extractTextFromBlock($block);

        $contentBlocks[$existingIndex]['text'] = $existingText . $newText;

        return $contentBlocks;
    }

    /**
     * @param array<string, mixed>[] $contentBlocks
     * @param array<string, mixed> $block
     * @return array<string, mixed>[]
     */
    private function createNewTextBlock(array $contentBlocks, array $block): array
    {
        $text = $this->extractTextFromBlock($block);

        $contentBlocks[] = [
            'type' => 'text',
            'text' => $text,
        ];

        return $contentBlocks;
    }

    /**
     * @param array<string, mixed> $block
     */
    private function extractTextFromBlock(array $block): string
    {
        $text = $block['text'] ?? '';

        if (is_string($text)) {
            return $text;
        }

        return is_scalar($text) ? (string) $text : '';
    }
}
