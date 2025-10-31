<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Client\SiliconFlowApiClient;
use Tourze\SiliconFlowBundle\Entity\ChatCompletionLog;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Exception\ApiException;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowConfigRepository;
use Tourze\SiliconFlowBundle\Service\ChatCompletionService;

#[CoversClass(ChatCompletionService::class)]
#[RunTestsInSeparateProcesses]
final class ChatCompletionServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Service 测试不需要额外的setup
    }

    public function testCreateCompletionSuccess(): void
    {
        $capturedOptions = [];
        $httpClient = new MockHttpClient(static function (string $method, string $url, array $options) use (&$capturedOptions): MockResponse {
            $capturedOptions = $options;

            return new MockResponse(json_encode([
                'id' => 'chatcmpl-123',
                'model' => 'deepseek-chat',
                'created' => 1700000000,
                'choices' => [
                    [
                        'index' => 0,
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Hi!',
                        ],
                        'finish_reason' => 'stop',
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 10,
                    'completion_tokens' => 5,
                    'total_tokens' => 15,
                ],
            ], JSON_THROW_ON_ERROR));
        });

        $apiClient = new SiliconFlowApiClient($httpClient, new NullLogger(), 30);

        $config = new SiliconFlowConfig();
        $config->setName('Primary');
        $config->setBaseUrl('https://custom.siliconflow.cn');
        $config->setApiToken('config-key');

        $configRepository = $this->createMock(SiliconFlowConfigRepository::class);
        $configRepository->expects($this->once())
            ->method('findActiveConfig')
            ->willReturn($config);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $capturedEntities = [];
        $entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$capturedEntities): void {
                $capturedEntities[] = $entity;
            });
        $entityManager->expects($this->once())
            ->method('flush');

        $logger = $this->createMock(LoggerInterface::class);

        $service = new ChatCompletionService($apiClient, $configRepository, $entityManager, $logger);

        $log = $service->createCompletion(
            'deepseek-chat',
            [
                ['role' => 'user', 'content' => 'Hello'],
            ],
            ['temperature' => 0.2],
        );

        self::assertSame('chatcmpl-123', $log->getRequestId());
        self::assertSame('deepseek-chat', $log->getModel());
        self::assertSame('success', $log->getStatus());
        self::assertSame(10, $log->getPromptTokens());
        self::assertSame(5, $log->getCompletionTokens());
        self::assertSame(15, $log->getTotalTokens());
        self::assertNotNull($log->getResponsePayload());
        self::assertCount(1, $capturedEntities);
        self::assertSame($log, $capturedEntities[0]);

        $headers = $capturedOptions['headers'] ?? [];
        self::assertIsArray($headers);
        if (array_is_list($headers)) {
            $authorization = null;
            foreach ($headers as $header) {
                self::assertIsString($header);
                if (str_starts_with($header, 'Authorization:')) {
                    $authorization = trim(substr($header, strlen('Authorization:')));
                    break;
                }
            }
            self::assertIsString($authorization);
            self::assertSame('bearer config-key', strtolower($authorization));
        } else {
            $normalizedHeaders = array_change_key_case($headers, CASE_LOWER);
            $authValue = $normalizedHeaders['authorization'] ?? '';
            self::assertIsString($authValue);
            self::assertSame('bearer config-key', strtolower($authValue));
        }

    }

    public function testCreateCompletionFailure(): void
    {
        $this->expectException(ApiException::class);

        $httpClient = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['error' => ['message' => 'Network error']], JSON_THROW_ON_ERROR),
            ['http_code' => 500],
        ));
        $apiClient = new SiliconFlowApiClient($httpClient, new NullLogger(), 30);

        $config = new SiliconFlowConfig();
        $config->setName('Primary');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $configRepository = $this->createMock(SiliconFlowConfigRepository::class);
        $configRepository->expects($this->once())
            ->method('findActiveConfig')
            ->willReturn($config);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $capturedEntities = [];
        $entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$capturedEntities): void {
                $capturedEntities[] = $entity;
            });
        $entityManager->expects($this->once())
            ->method('flush');

        $logger = $this->createMock(LoggerInterface::class);

        $service = new ChatCompletionService($apiClient, $configRepository, $entityManager, $logger);

        try {
            $service->createCompletion('deepseek-chat', [
                ['role' => 'user', 'content' => 'ping'],
            ]);
        } finally {
            self::assertCount(1, $capturedEntities);
            $log = $capturedEntities[0];
            self::assertInstanceOf(ChatCompletionLog::class, $log);
            self::assertSame('failed', $log->getStatus());
            self::assertStringContainsString('Network error', (string) $log->getErrorMessage());
            self::assertNull($log->getResponsePayload());
        }
    }
}
