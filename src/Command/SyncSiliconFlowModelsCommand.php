<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\SiliconFlowBundle\Client\SiliconFlowApiClient;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowModel;
use Tourze\SiliconFlowBundle\Exception\ApiException;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowConfigRepository;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowModelRepository;
use Tourze\SiliconFlowBundle\Request\GetModelsRequest;

#[AsCommand(name: 'tourze:silicon-flow:sync-models', description: '同步 SiliconFlow 平台模型列表至本地缓存')]
final class SyncSiliconFlowModelsCommand extends Command
{
    public function __construct(
        private readonly SiliconFlowConfigRepository $configRepository,
        private readonly SiliconFlowModelRepository $modelRepository,
        private readonly SiliconFlowApiClient $apiClient,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $configs = $this->configRepository->findActiveConfigs();

        if ([] === $configs) {
            $io->warning('未找到可用的 SiliconFlow 配置，已跳过同步。');

            return self::SUCCESS;
        }

        $syncResult = $this->syncModelsFromConfigs($configs, $io);

        if (!$syncResult['hadSuccessfulResponse']) {
            $io->warning('所有配置请求均失败，已保留现有模型状态。');

            return self::FAILURE;
        }

        $this->entityManager->flush();

        $activeIds = array_keys($syncResult['processedModelIds']);
        $this->modelRepository->deactivateAllExcept($activeIds);

        $io->success(sprintf('模型同步完成：新增 %d 条，更新 %d 条，保留激活 %d 条。',
            $syncResult['createdCount'], $syncResult['updatedCount'], count($activeIds)));

        return self::SUCCESS;
    }

    /**
     * 从所有配置中同步模型数据
     *
     * @param array<SiliconFlowConfig> $configs
     * @param SymfonyStyle $io
     * @return array{processedModelIds: array<string, bool>, createdCount: int, updatedCount: int, hadSuccessfulResponse: bool}
     */
    private function syncModelsFromConfigs(array $configs, SymfonyStyle $io): array
    {
        $processedModelIds = [];
        $createdCount = 0;
        $updatedCount = 0;
        $hadSuccessfulResponse = false;

        foreach ($configs as $config) {
            $configResult = $this->syncModelsFromConfig($config, $io, $processedModelIds);

            if (null === $configResult) {
                continue;
            }

            $hadSuccessfulResponse = true;
            $processedModelIds = array_merge($processedModelIds, $configResult['processedModelIds']);
            $createdCount += $configResult['createdCount'];
            $updatedCount += $configResult['updatedCount'];
        }

        return [
            'processedModelIds' => $processedModelIds,
            'createdCount' => $createdCount,
            'updatedCount' => $updatedCount,
            'hadSuccessfulResponse' => $hadSuccessfulResponse,
        ];
    }

    /**
     * 从单个配置中同步模型数据
     *
     * @param array<string, bool> $existingModelIds
     * @return array{processedModelIds: array<string, bool>, createdCount: int, updatedCount: int}|null
     */
    private function syncModelsFromConfig(SiliconFlowConfig $config, SymfonyStyle $io, array $existingModelIds): ?array
    {
        try {
            $response = $this->apiClient->request(new GetModelsRequest($config));
        } catch (ApiException $exception) {
            $io->error(sprintf('配置 %s 调用模型接口失败：%s', $config->getName(), $exception->getMessage()));

            return null;
        }

        $models = $response['data'] ?? [];
        if (!is_array($models)) {
            $io->warning(sprintf('配置 %s 返回的模型数据格式不正确，已跳过。', $config->getName()));

            return null;
        }

        return $this->processModelData($models, $existingModelIds);
    }

    /**
     * 处理模型数据数组
     *
     * @param array<mixed, mixed> $models
     * @param array<string, bool> $existingModelIds
     * @return array{processedModelIds: array<string, bool>, createdCount: int, updatedCount: int}
     */
    private function processModelData(array $models, array $existingModelIds): array
    {
        $processedModelIds = $existingModelIds;
        $createdCount = 0;
        $updatedCount = 0;

        foreach ($models as $modelData) {
            if (!is_array($modelData)) {
                continue;
            }

            $result = $this->processSingleModel($modelData, $processedModelIds);

            if (null === $result) {
                continue;
            }

            $processedModelIds = $result['processedModelIds'];
            $createdCount += $result['createdCount'];
            $updatedCount += $result['updatedCount'];
        }

        return [
            'processedModelIds' => $processedModelIds,
            'createdCount' => $createdCount,
            'updatedCount' => $updatedCount,
        ];
    }

    /**
     * 处理单个模型数据
     *
     * @param array<mixed, mixed> $modelData
     * @param array<string, bool> $processedModelIds
     * @return array{processedModelIds: array<string, bool>, createdCount: int, updatedCount: int}|null
     */
    private function processSingleModel(array $modelData, array $processedModelIds): ?array
    {
        $validatedModelData = $this->validateModelData($modelData);
        if (null === $validatedModelData) {
            return null;
        }

        $modelId = $this->extractModelId($validatedModelData);
        if (null === $modelId) {
            return null;
        }

        if (isset($processedModelIds[$modelId])) {
            return null;
        }

        $processedModelIds[$modelId] = true;

        $model = $this->modelRepository->findOneBy(['modelId' => $modelId]);
        $isNew = null === $model;

        if ($isNew) {
            $model = new SiliconFlowModel();
            $model->setModelId($modelId);
            $createdCount = 1;
            $updatedCount = 0;
        } else {
            $createdCount = 0;
            $updatedCount = 1;
        }

        $model->setObjectType($this->extractObjectType($validatedModelData, $model->getObjectType()));
        $model->setIsActive($this->isModelActive($validatedModelData));
        $model->setMetadata($validatedModelData);

        $this->modelRepository->save($model, false);

        return [
            'processedModelIds' => $processedModelIds,
            'createdCount' => $createdCount,
            'updatedCount' => $updatedCount,
        ];
    }

    /**
     * 验证并确保数组具有字符串键名。
     *
     * @param array<mixed, mixed> $modelData
     * @return array<string, mixed>|null
     */
    private function validateModelData(array $modelData): ?array
    {
        /** @var array<string, mixed> $validated */
        $validated = [];

        foreach ($modelData as $key => $value) {
            $validated[(string) $key] = $value;
        }

        return [] !== $validated ? $validated : null;
    }

    /**
     * @param array<string, mixed> $modelData
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
     * @param array<string, mixed> $modelData
     */
    private function extractObjectType(array $modelData, string $default): string
    {
        $object = $modelData['object'] ?? $modelData['object_type'] ?? $modelData['type'] ?? null;

        if (is_string($object) && '' !== trim($object)) {
            return trim($object);
        }

        return '' !== $default ? $default : 'model';
    }

    /**
     * @param array<string, mixed> $modelData
     */
    private function isModelActive(array $modelData): bool
    {
        $candidates = [
            $modelData['isActive'] ?? null,
            $modelData['is_active'] ?? null,
            $modelData['available'] ?? null,
            $modelData['online'] ?? null,
        ];

        foreach ($candidates as $value) {
            if (null !== $value) {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value;
            }
        }

        $status = $modelData['status'] ?? $modelData['state'] ?? null;
        if (is_string($status)) {
            return in_array(strtolower($status), ['active', 'available', 'online', 'enabled'], true);
        }

        return true;
    }
}
