<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Request;

use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Exception\ApiException;

final class SubmitVideoRequest extends AbstractSiliconFlowRequest
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        SiliconFlowConfig $config,
        private readonly array $payload,
        ?int $timeout = null,
    ) {
        parent::__construct($config, $timeout ?? 30);
    }

    public function getRequestPath(): string
    {
        return '/v1/video/submit';
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getRequestOptions(): array
    {
        return [
            'json' => $this->payload,
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

        if (!isset($decoded['requestId'])) {
            throw new ApiException('视频生成提交接口响应缺少 requestId 字段。');
        }

        /** @var array<string, mixed> $result */
        $result = $decoded;

        return $result;
    }

    public function validate(): void
    {
        parent::validate();

        if (!isset($this->payload['model']) || !is_string($this->payload['model']) || '' === trim($this->payload['model'])) {
            throw new ApiException('Video Submit 请求必须指定模型。');
        }

        if (!isset($this->payload['prompt']) || !is_string($this->payload['prompt']) || '' === trim($this->payload['prompt'])) {
            throw new ApiException('Video Submit 请求必须提供 prompt。');
        }

        if (!isset($this->payload['image_size']) || !is_string($this->payload['image_size']) || '' === trim($this->payload['image_size'])) {
            throw new ApiException('Video Submit 请求必须指定 image_size。');
        }

        if (1 !== preg_match('/^\d+x\d+$/', $this->payload['image_size'])) {
            throw new ApiException('image_size 必须符合 widthxheight 格式，例如 1280x720。');
        }
    }
}
