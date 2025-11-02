<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Request;

use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Exception\ApiException;

final class CreateChatCompletionRequest extends AbstractSiliconFlowRequest
{
    /**
     * @param array<int, array<string, mixed>> $messages
     * @param array<string, mixed> $options
     */
    public function __construct(
        SiliconFlowConfig $config,
        private readonly string $model,
        private readonly array $messages,
        private readonly array $options = [],
        ?int $timeout = null,
    ) {
        parent::__construct($config, $timeout ?? 30);
    }

    public function getRequestPath(): string
    {
        return '/v1/chat/completions';
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

    public function parseError(string $content, int $statusCode): string
    {
        return parent::parseError($content, $statusCode);
    }

    public function validate(): void
    {
        parent::validate();

        if ('' === trim($this->model)) {
            throw new ApiException('Chat Completions 请求必须指定模型。');
        }

        if ([] === $this->messages) {
            throw new ApiException('Chat Completions 请求必须包含至少一条 message。');
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

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
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

        // Ensure we have string keys by validating the structure
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

        $streamData = $this->initializeStreamData();
        $choices = [];
        $finishReasons = [];

        foreach ($lines as $rawLine) {
            $decoded = $this->parseLineToJson($rawLine);
            if (null === $decoded) {
                continue;
            }

            $streamData = $this->updateBasicStreamData($streamData, $decoded);
            [$choices, $finishReasons] = $this->processStreamChoices($decoded, $choices, $finishReasons);
            $streamData = $this->updateStreamUsage($streamData, $decoded);
        }

        $normalizedChoices = $this->normalizeChoices($choices, $finishReasons);

        return [
            'id' => $streamData['id'],
            'object' => 'chat.completion',
            'created' => $streamData['created'],
            'model' => $streamData['model'],
            'choices' => $normalizedChoices,
            'usage' => $streamData['usage'],
        ];
    }

    /**
     * @return string[]
     */
    private function splitContentIntoLines(string $content): array
    {
        $lines = preg_split('/\r?\n/', $content);
        if (false === $lines) {
            $lines = [];
        }

        return $lines;
    }

    /**
     * @return array{id: mixed, model: mixed, created: mixed, usage: array<string, mixed>}
     */
    private function initializeStreamData(): array
    {
        return [
            'id' => null,
            'model' => null,
            'created' => null,
            'usage' => [
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseLineToJson(string $rawLine): ?array
    {
        $line = trim($rawLine);
        if ('' === $line || !str_starts_with($line, 'data:')) {
            return null;
        }

        $payload = trim(substr($line, 5));
        if ('' === $payload || '[DONE]' === $payload) {
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
     * @param array{id: mixed, model: mixed, created: mixed, usage: array<string, mixed>} $streamData
     * @param array<string, mixed> $decoded
     * @return array{id: mixed, model: mixed, created: mixed, usage: array<string, mixed>}
     */
    private function updateBasicStreamData(array $streamData, array $decoded): array
    {
        $streamData['id'] ??= $decoded['id'] ?? null;
        $streamData['model'] ??= $decoded['model'] ?? null;
        $streamData['created'] ??= $decoded['created'] ?? null;

        return $streamData;
    }

    /**
     * @param array<string, mixed> $decoded
     * @param array<int, array<string, mixed>> $choices
     * @param array<int, mixed> $finishReasons
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, mixed>}
     */
    private function processStreamChoices(array $decoded, array $choices, array $finishReasons): array
    {
        $decodedChoices = $decoded['choices'] ?? [];
        if (!is_array($decodedChoices)) {
            return [$choices, $finishReasons];
        }

        foreach ($decodedChoices as $choice) {
            if (!is_array($choice)) {
                continue;
            }

            // 确保 $choice 是 array<string, mixed> 类型
            /** @var array<string, mixed> $choice */
            $index = isset($choice['index']) && is_int($choice['index']) ? $choice['index'] : 0;
            $choices = $this->ensureChoiceExists($choices, $choice, $index);
            $choices = $this->updateChoiceContent($choices, $choice, $index);
            [$choices, $finishReasons] = $this->updateChoiceFinishReason($choices, $choice, $index, $finishReasons);
        }

        return [$choices, $finishReasons];
    }

    /**
     * @param array<int, array<string, mixed>> $choices
     * @param array<string, mixed> $choice
     * @return array<int, array<string, mixed>>
     */
    private function ensureChoiceExists(array $choices, array $choice, int $index): array
    {
        if (!isset($choices[$index])) {
            $delta = isset($choice['delta']) && is_array($choice['delta']) ? $choice['delta'] : [];
            $role = isset($delta['role']) && is_string($delta['role']) ? $delta['role'] : 'assistant';

            $choices[$index] = [
                'index' => $index,
                'message' => [
                    'role' => $role,
                    'content' => '',
                ],
                'finish_reason' => null,
            ];
        }

        return $choices;
    }

    /**
     * @param array<int, array<string, mixed>> $choices
     * @param array<string, mixed> $choice
     * @return array<int, array<string, mixed>>
     */
    private function updateChoiceContent(array $choices, array $choice, int $index): array
    {
        $delta = $this->getDeltaFromChoice($choice);

        $choices = $this->updateRole($choices, $delta, $index);
        $choices = $this->updateContent($choices, $delta, $index);

        return $choices;
    }

    /**
     * @param array<string, mixed> $choice
     * @return array<string, mixed>
     */
    private function getDeltaFromChoice(array $choice): array
    {
        /** @var array<string, mixed> $delta */
        $delta = isset($choice['delta']) && is_array($choice['delta']) ? $choice['delta'] : [];

        return $delta;
    }

    /**
     * @param array<int, array<string, mixed>> $choices
     * @return array<int, array<string, mixed>>
     */
    private function ensureMessageExists(array $choices, int $index): array
    {
        if (!isset($choices[$index]['message'])) {
            $choices[$index]['message'] = ['role' => '', 'content' => ''];
        }

        return $choices;
    }

    /**
     * @param array<int, array<string, mixed>> $choices
     * @param array<string, mixed> $delta
     * @return array<int, array<string, mixed>>
     */
    private function updateRole(array $choices, array $delta, int $index): array
    {
        if (!array_key_exists('role', $delta)) {
            return $choices;
        }

        $role = $delta['role'];
        if (is_string($role) && '' !== $role) {
            $choices = $this->ensureMessageExists($choices, $index);
            $message = $choices[$index]['message'];
            if (is_array($message)) {
                $message['role'] = $role;
                $choices[$index]['message'] = $message;
            }
        }

        return $choices;
    }

    /**
     * @param array<int, array<string, mixed>> $choices
     * @param array<string, mixed> $delta
     * @return array<int, array<string, mixed>>
     */
    private function updateContent(array $choices, array $delta, int $index): array
    {
        if (!array_key_exists('content', $delta)) {
            return $choices;
        }

        $choices = $this->ensureMessageExists($choices, $index);

        $content = $delta['content'];
        $currentContent = $this->getCurrentContent($choices, $index);

        $message = $choices[$index]['message'];
        if (is_array($message)) {
            $message['content'] = $this->appendContent($currentContent, $content);
            $choices[$index]['message'] = $message;
        }

        return $choices;
    }

    /**
     * @param array<int, array<string, mixed>> $choices
     * @return string
     */
    private function getCurrentContent(array $choices, int $index): string
    {
        $message = $choices[$index]['message'] ?? [];
        if (!is_array($message)) {
            return '';
        }

        $currentContent = $message['content'] ?? '';

        return is_string($currentContent) ? $currentContent : (is_scalar($currentContent) ? (string) $currentContent : '');
    }

    /**
     * @param mixed $content
     */
    private function appendContent(string $currentContent, $content): string
    {
        if (is_string($content)) {
            return $currentContent . $content;
        }

        return $currentContent . (is_scalar($content) ? (string) $content : '');
    }

    /**
     * @param array<int, array<string, mixed>> $choices
     * @param array<string, mixed> $choice
     * @param array<int, mixed> $finishReasons
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, mixed>}
     */
    private function updateChoiceFinishReason(array $choices, array $choice, int $index, array $finishReasons): array
    {
        if (array_key_exists('finish_reason', $choice) && null !== $choice['finish_reason']) {
            $choices[$index]['finish_reason'] = $choice['finish_reason'];
            $finishReasons[$index] = $choice['finish_reason'];
        }

        return [$choices, $finishReasons];
    }

    /**
     * @param array{id: mixed, model: mixed, created: mixed, usage: array<string, mixed>} $streamData
     * @param array<string, mixed> $decoded
     * @return array{id: mixed, model: mixed, created: mixed, usage: array<string, mixed>}
     */
    private function updateStreamUsage(array $streamData, array $decoded): array
    {
        if (isset($decoded['usage']) && is_array($decoded['usage'])) {
            $streamData['usage'] = array_merge($streamData['usage'], array_intersect_key($decoded['usage'], $streamData['usage']));
        }

        return $streamData;
    }

    /**
     * @param array<int, array<string, mixed>> $choices
     * @param array<int, mixed> $finishReasons
     * @return array<int, array<string, mixed>>
     */
    private function normalizeChoices(array $choices, array $finishReasons): array
    {
        ksort($choices);
        $normalizedChoices = array_values($choices);

        foreach ($normalizedChoices as $idx => $choice) {
            if (isset($finishReasons[$idx])) {
                $normalizedChoices[$idx]['finish_reason'] = $finishReasons[$idx];
            }
        }

        return $normalizedChoices;
    }
}
