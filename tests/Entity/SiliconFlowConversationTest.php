<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Entity;

use BizUserBundle\Entity\BizUser;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConversation;

/**
 * SiliconFlow 对话实体测试
 */
#[CoversClass(SiliconFlowConversation::class)]
class SiliconFlowConversationTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new SiliconFlowConversation();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'conversationType' => ['conversationType', SiliconFlowConversation::TYPE_CONTINUOUS],
            'model' => ['model', 'gpt-4'],
            'question' => ['question', '你好，请帮我解答一个问题'],
            'answer' => ['answer', '当然可以，我很乐意帮助您'],
            'contextSnapshot' => ['contextSnapshot', '之前的对话历史快照'],
        ];
    }

    /**
     * 测试常量定义
     */
    public function testConstants(): void
    {
        self::assertSame('single', SiliconFlowConversation::TYPE_SINGLE);
        self::assertSame('continuous', SiliconFlowConversation::TYPE_CONTINUOUS);
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $entity = $this->createEntity();
        $shortQuestion = 'Hello World';
        $entity->setQuestion($shortQuestion);
        self::assertSame($shortQuestion, (string) $entity);

        // 测试长文本截断
        $longQuestion = str_repeat('这是一个非常长的问题，', 20);
        $entity->setQuestion($longQuestion);
        $result = (string) $entity;
        self::assertStringEndsWith('...', $result);
        self::assertLessThanOrEqual(53, mb_strlen($result)); // 50 + '...'
    }

    /**
     * 测试ConversationType的getter和setter
     */
    public function testConversationType(): void
    {
        $entity = $this->createEntity();
        $entity->setConversationType(SiliconFlowConversation::TYPE_CONTINUOUS);
        self::assertSame(SiliconFlowConversation::TYPE_CONTINUOUS, $entity->getConversationType());

        $entity->setConversationType(SiliconFlowConversation::TYPE_SINGLE);
        self::assertSame(SiliconFlowConversation::TYPE_SINGLE, $entity->getConversationType());
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
     * 测试Question的getter和setter
     */
    public function testQuestion(): void
    {
        $entity = $this->createEntity();
        $question = '你好，请帮我解答一个问题';
        $entity->setQuestion($question);
        self::assertSame($question, $entity->getQuestion());
    }

    /**
     * 测试Answer的getter和setter
     */
    public function testAnswer(): void
    {
        $entity = $this->createEntity();
        $answer = '当然可以，我很乐意帮助您';
        $entity->setAnswer($answer);
        self::assertSame($answer, $entity->getAnswer());

        $entity->setAnswer(null);
        self::assertNull($entity->getAnswer());
    }

    /**
     * 测试Sender的getter和setter
     */
    public function testSender(): void
    {
        $entity = $this->createEntity();
        $mockUser = $this->createMock(BizUser::class);
        $entity->setSender($mockUser);
        self::assertSame($mockUser, $entity->getSender());
    }

    /**
     * 测试Context的getter和setter
     */
    public function testContext(): void
    {
        $entity = $this->createEntity();
        $contextConversation = new SiliconFlowConversation();
        $entity->setContext($contextConversation);
        self::assertSame($contextConversation, $entity->getContext());

        $entity->setContext(null);
        self::assertNull($entity->getContext());
    }

    /**
     * 测试ContextSnapshot的getter和setter
     */
    public function testContextSnapshot(): void
    {
        $entity = $this->createEntity();
        $snapshot = '之前的对话历史快照';
        $entity->setContextSnapshot($snapshot);
        self::assertSame($snapshot, $entity->getContextSnapshot());

        $entity->setContextSnapshot(null);
        self::assertNull($entity->getContextSnapshot());
    }

    /**
     * 测试完整的实体数据设置
     */
    public function testCompleteEntity(): void
    {
        $entity = $this->createEntity();
        $contextConversation = new SiliconFlowConversation();
        $mockUser = $this->createMock(BizUser::class);

        $entity->setConversationType(SiliconFlowConversation::TYPE_CONTINUOUS);
        $entity->setModel('gpt-4');
        $entity->setQuestion('这是一个测试问题');
        $entity->setAnswer('这是一个测试回答');
        $entity->setSender($mockUser);
        $entity->setContext($contextConversation);
        $entity->setContextSnapshot('历史快照');

        self::assertSame(SiliconFlowConversation::TYPE_CONTINUOUS, $entity->getConversationType());
        self::assertSame('gpt-4', $entity->getModel());
        self::assertSame('这是一个测试问题', $entity->getQuestion());
        self::assertSame('这是一个测试回答', $entity->getAnswer());
        self::assertSame($mockUser, $entity->getSender());
        self::assertSame($contextConversation, $entity->getContext());
        self::assertSame('历史快照', $entity->getContextSnapshot());
    }
}