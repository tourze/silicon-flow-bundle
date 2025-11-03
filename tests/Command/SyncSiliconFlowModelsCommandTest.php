<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Tourze\SiliconFlowBundle\Client\SiliconFlowApiClient;
use Tourze\SiliconFlowBundle\Command\SyncSiliconFlowModelsCommand;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowModel;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowConfigRepository;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowModelRepository;

#[CoversClass(SyncSiliconFlowModelsCommand::class)]
#[RunTestsInSeparateProcesses]
final class SyncSiliconFlowModelsCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // 通用初始化，具体 Mock 在各测试方法中注入
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(SyncSiliconFlowModelsCommand::class);
        return new CommandTester($command);
    }

    public function testExecuteWithNoActiveConfigs(): void
    {
        $configRepository = $this->createMock(SiliconFlowConfigRepository::class);
        $configRepository
            ->expects($this->once())
            ->method('findActiveConfigs')
            ->willReturn([]);

        self::getContainer()->set(SiliconFlowConfigRepository::class, $configRepository);

        $commandTester = $this->getCommandTester();
        $result = $commandTester->execute([]);

        self::assertEquals(0, $result);
        self::assertStringContainsString('未找到可用的 SiliconFlow 配置', $commandTester->getDisplay());
    }

    public function testExecuteWithSuccessfulSync(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('test-config');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        // 1) 注入配置仓库 Mock
        $configRepository = $this->createMock(SiliconFlowConfigRepository::class);
        $configRepository
            ->expects($this->once())
            ->method('findActiveConfigs')
            ->willReturn([$config]);
        self::getContainer()->set(SiliconFlowConfigRepository::class, $configRepository);

        // 2) 注入模型仓库 Mock
        /** @var MockObject&SiliconFlowModel $existingModel */
        $existingModel = $this->createMock(SiliconFlowModel::class);
        self::assertInstanceOf(SiliconFlowModel::class, $existingModel);
        $existingModel->method('getObjectType')->willReturn('model');

        $modelRepository = $this->createMock(SiliconFlowModelRepository::class);
        $modelRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnMap([
                [['modelId' => 'test-model-1'], $existingModel],
                [['modelId' => 'test-model-2'], null]
            ]);
        $modelRepository
            ->expects($this->exactly(2))
            ->method('save')
            ->with(self::isInstanceOf(SiliconFlowModel::class), false);
        $modelRepository
            ->expects($this->once())
            ->method('deactivateAllExcept')
            ->with(['test-model-1', 'test-model-2']);
        self::getContainer()->set(SiliconFlowModelRepository::class, $modelRepository);

        // 3) 注入 SiliconFlowApiClient（使用 MockHttpClient）
        $apiResponse = [
            'data' => [
                [
                    'id' => 'test-model-1',
                    'object' => 'model',
                    'isActive' => true,
                    'name' => 'Test Model 1'
                ],
                [
                    'model' => 'test-model-2',
                    'object_type' => 'chat',
                    'available' => true,
                    'description' => 'Test Model 2'
                ]
            ]
        ];

        $mockHttpClient = new MockHttpClient(function (string $method, string $url, array $options) use ($apiResponse): MockResponse {
            $responseBody = json_encode($apiResponse);
            if ($responseBody === false) {
                $responseBody = '{"error": "JSON encode failed"}';
            }
            return new MockResponse($responseBody, ['http_code' => 200]);
        });

        $logger = self::getService(LoggerInterface::class);
        $apiClient = new SiliconFlowApiClient($mockHttpClient, $logger, 30);
        self::getContainer()->set(SiliconFlowApiClient::class, $apiClient);

        // 4) 注入 EntityManager Mock
        /** @var MockObject&EntityManagerInterface $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('flush');
        self::getContainer()->set(EntityManagerInterface::class, $entityManager);

        // 5) 执行
        $commandTester = $this->getCommandTester();
        $result = $commandTester->execute([]);

        self::assertEquals(0, $result);
        self::assertStringContainsString('模型同步完成：新增 1 条，更新 1 条，保留激活 2 条', $commandTester->getDisplay());
    }

    public function testExecuteWithApiError(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('test-config');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('invalid-token');

        // 1) 注入配置仓库 Mock
        $configRepository = $this->createMock(SiliconFlowConfigRepository::class);
        $configRepository
            ->expects($this->once())
            ->method('findActiveConfigs')
            ->willReturn([$config]);
        self::getContainer()->set(SiliconFlowConfigRepository::class, $configRepository);

        // 2) 注入模型仓库 Mock
        $modelRepository = $this->createMock(SiliconFlowModelRepository::class);
        $modelRepository
            ->expects($this->never())
            ->method('save');
        self::getContainer()->set(SiliconFlowModelRepository::class, $modelRepository);

        // 3) 注入返回 401 错误的 ApiClient
        $mockHttpClient = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            return new MockResponse('Unauthorized', [
                'http_code' => 401,
                'response_headers' => ['content-type' => 'application/json']
            ]);
        });

        $logger = self::getService(LoggerInterface::class);
        $apiClient = new SiliconFlowApiClient($mockHttpClient, $logger, 30);
        self::getContainer()->set(SiliconFlowApiClient::class, $apiClient);

        // 4) 执行
        $commandTester = $this->getCommandTester();
        $result = $commandTester->execute([]);

        self::assertEquals(1, $result);
        self::assertStringContainsString('所有配置请求均失败', $commandTester->getDisplay());
        self::assertStringContainsString('调用模型接口失败', $commandTester->getDisplay());
    }

    public function testExecuteWithInvalidApiResponse(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('test-config');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        // 1) 注入配置仓库 Mock
        $configRepository = $this->createMock(SiliconFlowConfigRepository::class);
        $configRepository
            ->expects($this->once())
            ->method('findActiveConfigs')
            ->willReturn([$config]);
        self::getContainer()->set(SiliconFlowConfigRepository::class, $configRepository);

        // 2) 注入模型仓库 Mock
        $modelRepository = $this->createMock(SiliconFlowModelRepository::class);
        $modelRepository
            ->expects($this->never())
            ->method('save');
        self::getContainer()->set(SiliconFlowModelRepository::class, $modelRepository);

        // 3) 注入返回无效响应的 ApiClient
        $apiResponse = [
            'error' => 'Invalid response'
        ];

        $mockHttpClient = new MockHttpClient(function (string $method, string $url, array $options) use ($apiResponse): MockResponse {
            $responseBody = json_encode($apiResponse);
            if ($responseBody === false) {
                $responseBody = '{"error": "JSON encode failed"}';
            }
            return new MockResponse($responseBody, ['http_code' => 200]);
        });

        $logger = self::getService(LoggerInterface::class);
        $apiClient = new SiliconFlowApiClient($mockHttpClient, $logger, 30);
        self::getContainer()->set(SiliconFlowApiClient::class, $apiClient);

        // 4) 执行
        $commandTester = $this->getCommandTester();
        $result = $commandTester->execute([]);

        self::assertEquals(1, $result);
        self::assertStringContainsString('所有配置请求均失败', $commandTester->getDisplay());
        self::assertStringContainsString('调用模型接口失败', $commandTester->getDisplay());
    }

}
