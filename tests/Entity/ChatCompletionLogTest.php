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
        $this->assertInstanceOf(ChatCompletionLog::class, $this->entity);
        $this->assertNull($this->entity->getId());
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $this->entity->setModel('gpt-3.5-turbo');
        $this->assertSame('gpt-3.5-turbo', (string) $this->entity);
    }

    /**
     * 测试Request ID的getter和setter
     */
    public function testRequestId(): void
    {
        $requestId = 'req_123456789';
        $this->entity->setRequestId($requestId);
        $this->assertSame($requestId, $this->entity->getRequestId());

        $this->entity->setRequestId(null);
        $this->assertNull($this->entity->getRequestId());
    }

    /**
     * 测试Model的getter和setter
     */
    public function testModel(): void
    {
        $model = 'gpt-4-turbo';
        $this->entity->setModel($model);
        $this->assertSame($model, $this->entity->getModel());
    }

    /**
     * 测试Status的getter和setter
     */
    public function testStatus(): void
    {
        $status = 'failed';
        $this->entity->setStatus($status);
        $this->assertSame($status, $this->entity->getStatus());
    }

    /**
     * 测试Request Payload的getter和setter
     */
    public function testRequestPayload(): void
    {
        $payload = ['messages' => [['role' => 'user', 'content' => 'Hello']]];
        $this->entity->setRequestPayload($payload);
        $this->assertSame($payload, $this->entity->getRequestPayload());
    }

    /**
     * 测试Response Payload的getter和setter
     */
    public function testResponsePayload(): void
    {
        $payload = ['choices' => [['message' => ['role' => 'assistant', 'content' => 'Hi!']]]];
        $this->entity->setResponsePayload($payload);
        $this->assertSame($payload, $this->entity->getResponsePayload());

        $this->entity->setResponsePayload(null);
        $this->assertNull($this->entity->getResponsePayload());
    }

    /**
     * 测试Error Message的getter和setter
     */
    public function testErrorMessage(): void
    {
        $errorMessage = 'API rate limit exceeded';
        $this->entity->setErrorMessage($errorMessage);
        $this->assertSame($errorMessage, $this->entity->getErrorMessage());

        $this->entity->setErrorMessage(null);
        $this->assertNull($this->entity->getErrorMessage());
    }

    /**
     * 测试Token相关字段的getter和setter
     */
    public function testTokens(): void
    {
        $this->entity->setPromptTokens(100);
        $this->assertSame(100, $this->entity->getPromptTokens());

        $this->entity->setCompletionTokens(50);
        $this->assertSame(50, $this->entity->getCompletionTokens());

        $this->entity->setTotalTokens(150);
        $this->assertSame(150, $this->entity->getTotalTokens());
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

        $this->assertSame('req_test123', $this->entity->getRequestId());
        $this->assertSame('gpt-4', $this->entity->getModel());
        $this->assertSame('success', $this->entity->getStatus());
        $this->assertSame(['test' => 'data'], $this->entity->getRequestPayload());
        $this->assertSame(['response' => 'data'], $this->entity->getResponsePayload());
        $this->assertSame(10, $this->entity->getPromptTokens());
        $this->assertSame(20, $this->entity->getCompletionTokens());
        $this->assertSame(30, $this->entity->getTotalTokens());
    }
}