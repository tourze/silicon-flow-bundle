<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Entity\ChatCompletionLog;

/**
 * SiliconFlow 聊天完成日志实体测试
 */
#[CoversClass(ChatCompletionLog::class)]
#[RunTestsInSeparateProcesses]
class ChatCompletionLogTest extends AbstractIntegrationTestCase
{
    private ChatCompletionLog $entity;

    protected function onSetUp(): void
    {
        $this->entity = new ChatCompletionLog();
    }

    /**
     * 测试实体实例化
     */
    public function testInstantiation(): void
    {
        self::assertInstanceOf(ChatCompletionLog::class, $this->entity);
        self::assertNull($this->entity->getId());
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $this->entity->setModel('gpt-3.5-turbo');
        self::assertSame('gpt-3.5-turbo', (string) $this->entity);
    }

    /**
     * 测试Request ID的getter和setter
     */
    public function testRequestId(): void
    {
        $requestId = 'req_123456789';
        $this->entity->setRequestId($requestId);
        self::assertSame($requestId, $this->entity->getRequestId());

        $this->entity->setRequestId(null);
        self::assertNull($this->entity->getRequestId());
    }

    /**
     * 测试Model的getter和setter
     */
    public function testModel(): void
    {
        $model = 'gpt-4-turbo';
        $this->entity->setModel($model);
        self::assertSame($model, $this->entity->getModel());
    }

    /**
     * 测试Status的getter和setter
     */
    public function testStatus(): void
    {
        $status = 'failed';
        $this->entity->setStatus($status);
        self::assertSame($status, $this->entity->getStatus());
    }

    /**
     * 测试Request Payload的getter和setter
     */
    public function testRequestPayload(): void
    {
        $payload = ['messages' => [['role' => 'user', 'content' => 'Hello']]];
        $this->entity->setRequestPayload($payload);
        self::assertSame($payload, $this->entity->getRequestPayload());
    }

    /**
     * 测试Response Payload的getter和setter
     */
    public function testResponsePayload(): void
    {
        $payload = ['choices' => [['message' => ['role' => 'assistant', 'content' => 'Hi!']]]];
        $this->entity->setResponsePayload($payload);
        self::assertSame($payload, $this->entity->getResponsePayload());

        $this->entity->setResponsePayload(null);
        self::assertNull($this->entity->getResponsePayload());
    }

    /**
     * 测试Error Message的getter和setter
     */
    public function testErrorMessage(): void
    {
        $errorMessage = 'API rate limit exceeded';
        $this->entity->setErrorMessage($errorMessage);
        self::assertSame($errorMessage, $this->entity->getErrorMessage());

        $this->entity->setErrorMessage(null);
        self::assertNull($this->entity->getErrorMessage());
    }

    /**
     * 测试Token相关字段的getter和setter
     */
    public function testTokens(): void
    {
        $this->entity->setPromptTokens(100);
        self::assertSame(100, $this->entity->getPromptTokens());

        $this->entity->setCompletionTokens(50);
        self::assertSame(50, $this->entity->getCompletionTokens());

        $this->entity->setTotalTokens(150);
        self::assertSame(150, $this->entity->getTotalTokens());
    }

    /**
     * 测试完整的实体数据设置
     */
    public function testCompleteEntity(): void
    {
        $this->entity->setRequestId('req_test123');
        $this->entity->setModel('gpt-4');
        $this->entity->setStatus('success');
        $this->entity->setRequestPayload(['test' => 'data']);
        $this->entity->setResponsePayload(['response' => 'data']);
        $this->entity->setPromptTokens(10);
        $this->entity->setCompletionTokens(20);
        $this->entity->setTotalTokens(30);

        self::assertSame('req_test123', $this->entity->getRequestId());
        self::assertSame('gpt-4', $this->entity->getModel());
        self::assertSame('success', $this->entity->getStatus());
        self::assertSame(['test' => 'data'], $this->entity->getRequestPayload());
        self::assertSame(['response' => 'data'], $this->entity->getResponsePayload());
        self::assertSame(10, $this->entity->getPromptTokens());
        self::assertSame(20, $this->entity->getCompletionTokens());
        self::assertSame(30, $this->entity->getTotalTokens());
    }
}