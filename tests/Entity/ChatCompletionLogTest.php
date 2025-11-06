<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\SiliconFlowBundle\Entity\ChatCompletionLog;

/**
 * SiliconFlow 聊天完成日志实体测试
 */
#[CoversClass(ChatCompletionLog::class)]
class ChatCompletionLogTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new ChatCompletionLog();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'requestId' => ['requestId', 'req_test123'],
            'model' => ['model', 'gpt-4'],
            'status' => ['status', 'success'],
            'requestPayload' => ['requestPayload', ['messages' => [['role' => 'user', 'content' => 'Hello']]]],
            'responsePayload' => ['responsePayload', ['choices' => [['message' => ['role' => 'assistant', 'content' => 'Hi!']]]]],
            'errorMessage' => ['errorMessage', 'API rate limit exceeded'],
            'promptTokens' => ['promptTokens', 100],
            'completionTokens' => ['completionTokens', 50],
            'totalTokens' => ['totalTokens', 150],
        ];
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $entity = $this->createEntity();
        $entity->setModel('gpt-3.5-turbo');
        self::assertSame('gpt-3.5-turbo', (string) $entity);
    }

    /**
     * 测试Request ID的getter和setter
     */
    public function testRequestId(): void
    {
        $entity = $this->createEntity();
        $requestId = 'req_123456789';
        $entity->setRequestId($requestId);
        self::assertSame($requestId, $entity->getRequestId());

        $entity->setRequestId(null);
        self::assertNull($entity->getRequestId());
    }

    /**
     * 测试Model的getter和setter
     */
    public function testModel(): void
    {
        $entity = $this->createEntity();
        $model = 'gpt-4-turbo';
        $entity->setModel($model);
        self::assertSame($model, $entity->getModel());
    }

    /**
     * 测试Status的getter和setter
     */
    public function testStatus(): void
    {
        $entity = $this->createEntity();
        $status = 'failed';
        $entity->setStatus($status);
        self::assertSame($status, $entity->getStatus());
    }

    /**
     * 测试Request Payload的getter和setter
     */
    public function testRequestPayload(): void
    {
        $entity = $this->createEntity();
        $payload = ['messages' => [['role' => 'user', 'content' => 'Hello']]];
        $entity->setRequestPayload($payload);
        self::assertSame($payload, $entity->getRequestPayload());
    }

    /**
     * 测试Response Payload的getter和setter
     */
    public function testResponsePayload(): void
    {
        $entity = $this->createEntity();
        $payload = ['choices' => [['message' => ['role' => 'assistant', 'content' => 'Hi!']]]];
        $entity->setResponsePayload($payload);
        self::assertSame($payload, $entity->getResponsePayload());

        $entity->setResponsePayload(null);
        self::assertNull($entity->getResponsePayload());
    }

    /**
     * 测试Error Message的getter和setter
     */
    public function testErrorMessage(): void
    {
        $entity = $this->createEntity();
        $errorMessage = 'API rate limit exceeded';
        $entity->setErrorMessage($errorMessage);
        self::assertSame($errorMessage, $entity->getErrorMessage());

        $entity->setErrorMessage(null);
        self::assertNull($entity->getErrorMessage());
    }

    /**
     * 测试Token相关字段的getter和setter
     */
    public function testTokens(): void
    {
        $entity = $this->createEntity();
        $entity->setPromptTokens(100);
        self::assertSame(100, $entity->getPromptTokens());

        $entity->setCompletionTokens(50);
        self::assertSame(50, $entity->getCompletionTokens());

        $entity->setTotalTokens(150);
        self::assertSame(150, $entity->getTotalTokens());
    }

    /**
     * 测试完整的实体数据设置
     */
    public function testCompleteEntity(): void
    {
        $entity = $this->createEntity();
        $entity->setRequestId('req_test123');
        $entity->setModel('gpt-4');
        $entity->setStatus('success');
        $entity->setRequestPayload(['test' => 'data']);
        $entity->setResponsePayload(['response' => 'data']);
        $entity->setPromptTokens(10);
        $entity->setCompletionTokens(20);
        $entity->setTotalTokens(30);

        self::assertSame('req_test123', $entity->getRequestId());
        self::assertSame('gpt-4', $entity->getModel());
        self::assertSame('success', $entity->getStatus());
        self::assertSame(['test' => 'data'], $entity->getRequestPayload());
        self::assertSame(['response' => 'data'], $entity->getResponsePayload());
        self::assertSame(10, $entity->getPromptTokens());
        self::assertSame(20, $entity->getCompletionTokens());
        self::assertSame(30, $entity->getTotalTokens());
    }
}