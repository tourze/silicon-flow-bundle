<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Symfony\Component\Console\Application;
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
final class SyncSiliconFlowModelsCommandTest extends AbstractIntegrationTestCase
{
    private CommandTester $commandTester;
    /** @var MockObject&SiliconFlowConfigRepository */
    private SiliconFlowConfigRepository $configRepository;
    /** @var MockObject&SiliconFlowModelRepository */
    private SiliconFlowModelRepository $modelRepository;
    private SiliconFlowApiClient $apiClient;
    /** @var MockObject&EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    protected function onSetUp(): void
    {
        $this->configRepository = $this->createMock(SiliconFlowConfigRepository::class);
        $this->assertInstanceOf(SiliconFlowConfigRepository::class, $this->configRepository);

        $this->modelRepository = $this->createMock(SiliconFlowModelRepository::class);
        $this->assertInstanceOf(SiliconFlowModelRepository::class, $this->modelRepository);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $this->entityManager);

        // 创建一个 MockHttpClient，默认返回成功响应
        $this->apiClient = new SiliconFlowApiClient(new MockHttpClient(), new NullLogger(), 30);

        $command = new SyncSiliconFlowModelsCommand(
            $this->configRepository,
            $this->modelRepository,
            $this->apiClient,
            $this->entityManager
        );

        $application = new Application();
        $application->add($command);

        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithNoActiveConfigs(): void
    {
        $this->configRepository
            ->expects($this->once())
            ->method('findActiveConfigs')
            ->willReturn([]);

        $result = $this->commandTester->execute([]);

        $this->assertEquals(0, $result);
        $this->assertStringContainsString('未找到可用的 SiliconFlow 配置', $this->commandTester->getDisplay());
    }

    public function testExecuteWithSuccessfulSync(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('test-config');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $this->configRepository
            ->expects($this->once())
            ->method('findActiveConfigs')
            ->willReturn([$config]);

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

        $apiClient = new SiliconFlowApiClient($mockHttpClient, new NullLogger(), 30);

        $command = new SyncSiliconFlowModelsCommand(
            $this->configRepository,
            $this->modelRepository,
            $apiClient,
            $this->entityManager
        );

        $application = new Application();
        $application->add($command);
        $commandTester = new CommandTester($command);

        /** @var MockObject&SiliconFlowModel $existingModel */
        $existingModel = $this->createMock(SiliconFlowModel::class);
        $this->assertInstanceOf(SiliconFlowModel::class, $existingModel);
        $existingModel->method('getObjectType')->willReturn('model');

        $this->modelRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnMap([
                [['modelId' => 'test-model-1'], $existingModel],
                [['modelId' => 'test-model-2'], null]
            ]);

        $this->modelRepository
            ->expects($this->exactly(2))
            ->method('save')
            ->with(self::isInstanceOf(SiliconFlowModel::class), false);

        $this->modelRepository
            ->expects($this->once())
            ->method('deactivateAllExcept')
            ->with(['test-model-1', 'test-model-2']);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $commandTester->execute([]);

        $this->assertEquals(0, $result);
        $this->assertStringContainsString('模型同步完成：新增 1 条，更新 1 条，保留激活 2 条', $commandTester->getDisplay());
    }

    public function testExecuteWithApiError(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('test-config');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('invalid-token');

        $this->configRepository
            ->expects($this->once())
            ->method('findActiveConfigs')
            ->willReturn([$config]);

        $mockHttpClient = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            return new MockResponse('Unauthorized', [
                'http_code' => 401,
                'response_headers' => ['content-type' => 'application/json']
            ]);
        });

        $apiClient = new SiliconFlowApiClient($mockHttpClient, new NullLogger(), 30);

        $command = new SyncSiliconFlowModelsCommand(
            $this->configRepository,
            $this->modelRepository,
            $apiClient,
            $this->entityManager
        );

        $application = new Application();
        $application->add($command);
        $commandTester = new CommandTester($command);

        $this->modelRepository
            ->expects($this->never())
            ->method('save');

        $result = $commandTester->execute([]);

        $this->assertEquals(1, $result);
        $this->assertStringContainsString('所有配置请求均失败', $commandTester->getDisplay());
        $this->assertStringContainsString('调用模型接口失败', $commandTester->getDisplay());
    }

    public function testExecuteWithInvalidApiResponse(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('test-config');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $this->configRepository
            ->expects($this->once())
            ->method('findActiveConfigs')
            ->willReturn([$config]);

        // 返回缺少 data 字段的响应
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

        $apiClient = new SiliconFlowApiClient($mockHttpClient, new NullLogger(), 30);

        $command = new SyncSiliconFlowModelsCommand(
            $this->configRepository,
            $this->modelRepository,
            $apiClient,
            $this->entityManager
        );

        $application = new Application();
        $application->add($command);
        $commandTester = new CommandTester($command);

        $this->modelRepository
            ->expects($this->never())
            ->method('save');

        $result = $commandTester->execute([]);

        $this->assertEquals(1, $result);
        $this->assertStringContainsString('所有配置请求均失败', $commandTester->getDisplay());
        $this->assertStringContainsString('调用模型接口失败', $commandTester->getDisplay());
    }

    public function testValidateModelData(): void
    {
        $command = new SyncSiliconFlowModelsCommand(
            $this->configRepository,
            $this->modelRepository,
            $this->apiClient,
            $this->entityManager
        );

        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('validateModelData');
        $method->setAccessible(true);

        // 测试正常数据
        $input = ['id' => 'test', 'name' => 'Test Model'];
        $result = $method->invoke($command, $input);
        $this->assertEquals($input, $result);

        // 测试空数组
        $result = $method->invoke($command, []);
        $this->assertNull($result);

        // 测试包含数字键的数组
        $input = [0 => 'zero', 'id' => 'test'];
        $expected = ['0' => 'zero', 'id' => 'test'];
        $result = $method->invoke($command, $input);
        $this->assertEquals($expected, $result);
    }

    public function testExtractModelId(): void
    {
        $command = new SyncSiliconFlowModelsCommand(
            $this->configRepository,
            $this->modelRepository,
            $this->apiClient,
            $this->entityManager
        );

        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('extractModelId');
        $method->setAccessible(true);

        // 测试不同字段
        $this->assertEquals('test-id', $method->invoke($command, ['id' => 'test-id']));
        $this->assertEquals('test-model', $method->invoke($command, ['model' => 'test-model']));
        $this->assertEquals('test-modelId', $method->invoke($command, ['modelId' => 'test-modelId']));

        // 测试优先级
        $this->assertEquals('test-id', $method->invoke($command, ['id' => 'test-id', 'model' => 'test-model']));

        // 测试空值
        $this->assertNull($method->invoke($command, ['id' => '']));
        $this->assertNull($method->invoke($command, ['id' => '   ']));

        // 测试没有ID字段
        $this->assertNull($method->invoke($command, ['name' => 'test']));
    }

    public function testExtractObjectType(): void
    {
        $command = new SyncSiliconFlowModelsCommand(
            $this->configRepository,
            $this->modelRepository,
            $this->apiClient,
            $this->entityManager
        );

        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('extractObjectType');
        $method->setAccessible(true);

        // 测试不同字段
        $this->assertEquals('model', $method->invoke($command, ['object' => 'model'], ''));
        $this->assertEquals('chat', $method->invoke($command, ['object_type' => 'chat'], ''));
        $this->assertEquals('completion', $method->invoke($command, ['type' => 'completion'], ''));

        // 测试默认值
        $this->assertEquals('default-type', $method->invoke($command, ['name' => 'test'], 'default-type'));
        $this->assertEquals('model', $method->invoke($command, ['name' => 'test'], ''));

        // 测试空值
        $this->assertEquals('default-type', $method->invoke($command, ['object' => ''], 'default-type'));
        $this->assertEquals('default-type', $method->invoke($command, ['object' => '   '], 'default-type'));
    }

    public function testIsModelActive(): void
    {
        $command = new SyncSiliconFlowModelsCommand(
            $this->configRepository,
            $this->modelRepository,
            $this->apiClient,
            $this->entityManager
        );

        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('isModelActive');
        $method->setAccessible(true);

        // 测试布尔值字段
        $this->assertTrue($method->invoke($command, ['isActive' => true]));
        $this->assertFalse($method->invoke($command, ['isActive' => false]));
        $this->assertTrue($method->invoke($command, ['available' => true]));
        $this->assertFalse($method->invoke($command, ['online' => false]));

        // 测试字符串布尔值
        $this->assertTrue($method->invoke($command, ['isActive' => 'true']));
        $this->assertFalse($method->invoke($command, ['isActive' => 'false']));
        $this->assertTrue($method->invoke($command, ['isActive' => '1']));
        $this->assertFalse($method->invoke($command, ['isActive' => '0']));

        // 测试状态字段
        $this->assertTrue($method->invoke($command, ['status' => 'active']));
        $this->assertTrue($method->invoke($command, ['status' => 'AVAILABLE']));
        $this->assertTrue($method->invoke($command, ['status' => 'Online']));
        $this->assertFalse($method->invoke($command, ['status' => 'inactive']));
        $this->assertFalse($method->invoke($command, ['status' => 'offline']));

        // 测试默认值（没有活跃状态信息）
        $this->assertTrue($method->invoke($command, ['name' => 'test']));
    }
}
