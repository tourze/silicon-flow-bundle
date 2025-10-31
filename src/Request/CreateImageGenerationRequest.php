<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Request;

use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Exception\ApiException;

final class CreateImageGenerationRequest extends AbstractSiliconFlowRequest
{
    /**
     * @param array<string, mixed> $extraOptions
     */
    public function __construct(
        SiliconFlowConfig $config,
        private readonly string $model,
        private readonly string $prompt,
        private readonly ?string $negativePrompt = null,
        private readonly ?string $imageSize = null,
        private readonly ?int $batchSize = null,
        private readonly ?int $seed = null,
        private readonly ?int $numInferenceSteps = null,
        private readonly array $extraOptions = [],
        ?int $timeout = null,
    ) {
        parent::__construct($config, $timeout ?? 30);
    }

    public function getRequestPath(): string
    {
        return '/v1/images/generations';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = array_merge(
            $this->extraOptions,
            [
                'model' => $this->model,
                'prompt' => $this->prompt,
            ],
        );

        if (null !== $this->negativePrompt && '' !== $this->negativePrompt) {
            $payload['negative_prompt'] = $this->negativePrompt;
        }

        if (null !== $this->imageSize && '' !== $this->imageSize) {
            $payload['image_size'] = $this->imageSize;
        }

        if (null !== $this->batchSize) {
            $payload['batch_size'] = $this->batchSize;
        }

        if (null !== $this->seed) {
            $payload['seed'] = $this->seed;
        }

        if (null !== $this->numInferenceSteps) {
            $payload['num_inference_steps'] = $this->numInferenceSteps;
        }

        return $payload;
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

        if (!isset($decoded['images']) || !is_array($decoded['images'])) {
            throw new ApiException('SiliconFlow 图片生成响应缺少 images 字段。');
        }

        /** @var array<string, mixed> $result */
        $result = $decoded;

        return $result;
    }

    public function validatesBatchSize(): void
    {
        if (null === $this->batchSize) {
            return;
        }

        if ($this->batchSize < 1 || $this->batchSize > 4) {
            throw new ApiException('批量生成数量 batch_size 需在 1 到 4 之间。');
        }
    }

    public function validate(): void
    {
        parent::validate();

        if ('' === trim($this->model)) {
            throw new ApiException('Image Generation 请求必须指定模型。');
        }

        if ('' === trim($this->prompt)) {
            throw new ApiException('Image Generation 请求必须提供 prompt。');
        }

        $this->validatesBatchSize();

        if (null !== $this->numInferenceSteps && ($this->numInferenceSteps < 1 || $this->numInferenceSteps > 100)) {
            throw new ApiException('num_inference_steps 需在 1 到 100 之间。');
        }

        if (null !== $this->imageSize && 1 !== preg_match('/^\d+x\d+$/', $this->imageSize)) {
            throw new ApiException('image_size 必须符合 widthxheight 格式，例如 1024x1024。');
        }
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function getNegativePrompt(): ?string
    {
        return $this->negativePrompt;
    }

    public function getImageSize(): ?string
    {
        return $this->imageSize;
    }

    public function getBatchSize(): ?int
    {
        return $this->batchSize;
    }

    public function getSeed(): ?int
    {
        return $this->seed;
    }

    public function getNumInferenceSteps(): ?int
    {
        return $this->numInferenceSteps;
    }

    /**
     * @return array<string, mixed>
     */
    public function getExtraOptions(): array
    {
        return $this->extraOptions;
    }
}
