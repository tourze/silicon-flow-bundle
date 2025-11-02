<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\SiliconFlowBundle\Client\SiliconFlowApiClient;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowModel;
use Tourze\SiliconFlowBundle\Exception\ApiException;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowConfigRepository;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowModelRepository;
use Tourze\SiliconFlowBundle\Request\GetModelsRequest;

#[When(env: 'dev')]
#[When(env: 'test')]
class SiliconFlowModelFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        /** @internal 用于查找活跃的 SiliconFlow 配置 */
        private readonly SiliconFlowConfigRepository $configRepository,
        /** @internal 用于操作 SiliconFlow 模型实体 */
        private readonly SiliconFlowModelRepository $modelRepository,
        /** @internal 用于调用 SiliconFlow API 获取模型列表 */
        private readonly SiliconFlowApiClient $apiClient,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $config = $this->configRepository->findActiveConfig();
        if (null === $config) {
            return;
        }

        $processedIds = $this->loadModelsForAllTypes($config, $manager);

        $manager->flush();

        if ([] !== $processedIds) {
            $this->modelRepository->deactivateAllExcept(array_keys($processedIds));
        }
    }

    /**
     * 为所有支持的类型加载模型
     *
     * @param ObjectManager $manager
     * @return array<string, bool>
     */
    private function loadModelsForAllTypes(SiliconFlowConfig $config, ObjectManager $manager): array
    {
        $processedIds = [];

        foreach (SiliconFlowModel::getSupportedTypes() as $type) {
            $typeProcessedIds = $this->loadModelsForType($config, $type, $manager);
            $processedIds = array_merge($processedIds, $typeProcessedIds);
        }

        return $processedIds;
    }

    /**
     * 为指定类型加载模型
     *
     * @param string $type
     * @param ObjectManager $manager
     * @return array<string, bool>
     */
    private function loadModelsForType(SiliconFlowConfig $config, string $type, ObjectManager $manager): array
    {
        try {
            $response = $this->apiClient->request(new GetModelsRequest($config, $type));
        } catch (ApiException $exception) {
            return [];
        }

        $models = $response['data'] ?? [];
        if (!is_array($models)) {
            return [];
        }

        return $this->processModelDataArray($models, $type, $manager);
    }

    /**
     * 处理模型数据数组
     *
     * @param array<mixed, mixed> $models
     * @param string $type
     * @param ObjectManager $manager
     * @return array<string, bool>
     */
    private function processModelDataArray(array $models, string $type, ObjectManager $manager): array
    {
        $processedIds = [];

        foreach ($models as $modelData) {
            if (!is_array($modelData)) {
                continue;
            }

            $modelId = $this->processModelData($modelData, $type, $manager);
            if (null !== $modelId) {
                $processedIds[$modelId] = true;
            }
        }

        return $processedIds;
    }

    /**
     * 处理单个模型数据
     *
     * @param array<mixed, mixed> $modelData
     * @param string $type
     * @param ObjectManager $manager
     * @return string|null
     */
    private function processModelData(array $modelData, string $type, ObjectManager $manager): ?string
    {
        /** @var array<string, mixed> $normalizedData */
        $normalizedData = array_combine(
            array_map('strval', array_keys($modelData)),
            array_values($modelData)
        );

        if (!is_array($normalizedData)) {
            return null;
        }

        $modelId = $this->extractModelId($normalizedData);
        if (null === $modelId) {
            return null;
        }

        $model = $this->modelRepository->findOneBy(['modelId' => $modelId]);
        if (null === $model) {
            $model = new SiliconFlowModel();
            $model->setModelId($modelId);
            $manager->persist($model);
        }

        $model->setType($type);
        $model->setObjectType($this->extractObjectType($normalizedData, $model->getObjectType()));
        $model->setIsActive($this->isModelActive($normalizedData));
        $model->setMetadata($normalizedData);

        return $modelId;
    }

    public static function getGroups(): array
    {
        return ['silicon_flow'];
    }

    /**
     * 从模型数据中提取模型ID
     *
     * 支持多种常见的ID字段名：id, model, modelId
     *
     * @param array<string, mixed> $modelData 从API返回的模型数据
     * @return string|null 提取到的模型ID，如果找不到则返回null
     */
    private function extractModelId(array $modelData): ?string
    {
        $candidates = [
            $modelData['id'] ?? null,
            $modelData['model'] ?? null,
            $modelData['modelId'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && '' !== trim($candidate)) {
                return trim($candidate);
            }
        }

        return null;
    }

    /**
     * 从模型数据中提取对象类型
     *
     * 支持多种常见的类型字段名：object, object_type, type
     *
     * @param array<string, mixed> $modelData 从API返回的模型数据
     * @param string $default 默认类型，如果为空则使用'model'作为默认值
     * @return string 提取到的对象类型
     */
    private function extractObjectType(array $modelData, string $default): string
    {
        $candidate = $modelData['object'] ?? $modelData['object_type'] ?? $modelData['type'] ?? null;

        if (is_string($candidate) && '' !== trim($candidate)) {
            return trim($candidate);
        }

        return '' !== $default ? $default : 'model';
    }

    /**
     * 判断模型是否处于活跃状态
     *
     * 支持多种布尔字段名和状态字符串：
     * - 布尔字段：isActive, is_active, available, online
     * - 状态字符串：active, available, online, enabled
     *
     * @param array<string, mixed> $modelData 从API返回的模型数据
     * @return bool 模型是否活跃，默认为true
     */
    private function isModelActive(array $modelData): bool
    {
        $candidates = [
            $modelData['isActive'] ?? null,
            $modelData['is_active'] ?? null,
            $modelData['available'] ?? null,
            $modelData['online'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (null !== $candidate) {
                return filter_var($candidate, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $candidate;
            }
        }

        $status = $modelData['status'] ?? $modelData['state'] ?? null;
        if (is_string($status)) {
            return in_array(strtolower($status), ['active', 'available', 'online', 'enabled'], true);
        }

        return true;
    }
}
