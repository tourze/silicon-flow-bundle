<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Request;

use InvalidArgumentException;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowModel;
use Tourze\SiliconFlowBundle\Exception\ApiException;

final class GetModelsRequest extends AbstractSiliconFlowRequest
{
    /**
     * @param array<string, mixed> $queryParams
     */
    public function __construct(
        SiliconFlowConfig $config,
        private readonly ?string $type = null,
        private readonly array $queryParams = [],
        ?int $timeout = null,
    ) {
        if (null !== $this->type && !in_array($this->type, SiliconFlowModel::getSupportedTypes(), true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported SiliconFlow model type: %s', $this->type));
        }

        parent::__construct($config, $timeout ?? 30);
    }

    public function getRequestPath(): string
    {
        return '/v1/models';
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getRequestOptions(): array
    {
        $query = $this->queryParams;
        if (null !== $this->type) {
            $query['type'] = $this->type;
        }

        return [
            'query' => $query,
            'timeout' => $this->getTimeout(),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getApiToken(),
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

        if (!isset($decoded['data']) || !is_array($decoded['data'])) {
            throw new ApiException('Models 接口响应缺少 data 字段。');
        }

        /** @var array<string, mixed> $result */
        $result = $decoded;

        return $result;
    }
}
