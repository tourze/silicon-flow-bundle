<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Exception\ApiException;

/**
 * SiliconFlow API 请求基类
 *
 * 提供所有 SiliconFlow API 请求的通用功能：
 * - 配置管理和认证信息处理
 * - 超时控制和错误处理
 * - 统一的请求格式和响应解析
 * - 流式和 JSON 响应的自动检测
 */
abstract class AbstractSiliconFlowRequest extends ApiRequest
{
    public function __construct(
        protected readonly SiliconFlowConfig $config,
        private readonly int $timeout = 30,
    ) {
    }

    public function getConfig(): SiliconFlowConfig
    {
        return $this->config;
    }

    public function getBaseUrl(): string
    {
        $baseUrl = trim($this->config->getBaseUrl());

        return '' === $baseUrl ? 'https://api.siliconflow.cn' : rtrim($baseUrl, '/');
    }

    public function getApiToken(): string
    {
        return $this->config->getApiToken();
    }

    public function getRequestMethod(): ?string
    {
        return 'POST';
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function validate(): void
    {
        if ('' === trim($this->getApiToken())) {
            throw new ApiException('SiliconFlow API Token 未配置，无法发起请求。');
        }
    }

    /**
     * @return array<string, mixed>
     */
    abstract public function parseResponse(string $content): array;

    public function parseError(string $content, int $statusCode): string
    {
        $decoded = json_decode($content, true);

        if (is_array($decoded) && isset($decoded['error']) && is_array($decoded['error']) && isset($decoded['error']['message'])) {
            $errorMessage = $decoded['error']['message'];
            if (is_string($errorMessage)) {
                return sprintf('SiliconFlow API 返回错误（HTTP %d）：%s', $statusCode, $errorMessage);
            }
        }

        return sprintf('SiliconFlow API 返回错误（HTTP %d）：%s', $statusCode, $content);
    }

    public function expectsStream(): bool
    {
        return false;
    }
}
