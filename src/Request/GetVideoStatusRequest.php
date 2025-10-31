<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Request;

use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Exception\ApiException;

final class GetVideoStatusRequest extends AbstractSiliconFlowRequest
{
    public function __construct(
        SiliconFlowConfig $config,
        private readonly string $requestId,
        ?int $timeout = null,
    ) {
        parent::__construct($config, $timeout ?? 30);
    }

    public function getRequestPath(): string
    {
        return '/v1/video/status';
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getRequestOptions(): array
    {
        return [
            'json' => ['requestId' => $this->requestId],
            'timeout' => $this->getTimeout(),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getApiToken(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function parseResponse(string $content): array
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

    public function validate(): void
    {
        parent::validate();

        if ('' === trim($this->requestId)) {
            throw new ApiException('Video Status 请求必须提供 requestId。');
        }
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
