<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Entity;

use BizUserBundle\Entity\BizUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConversation;

/**
 * SiliconFlow 对话实体测试
 */
#[CoversClass(SiliconFlowConversation::class)]
#[RunTestsInSeparateProcesses]
class SiliconFlowConversationTest extends AbstractIntegrationTestCase
{
    private SiliconFlowConversation $entity;
    private BizUser $mockUser;

    protected function onSetUp(): void
    {
        $this->entity = new SiliconFlowConversation();
        $this->mockUser = $this->createMock(BizUser::class);
    }

    /**
     * 测试实体实例化
     */
    public function testInstantiation(): void
    {
        self::assertInstanceOf(SiliconFlowConversation::class, $this->entity);
        self::assertNull($this->entity->getId());
        self::assertSame(SiliconFlowConversation::TYPE_SINGLE, $this->entity->getConversationType());
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
        $shortQuestion = 'Hello World';
        $this->entity->setQuestion($shortQuestion);
        self::assertSame($shortQuestion, (string) $this->entity);

        // 测试长文本截断
        $longQuestion = str_repeat('这是一个非常长的问题，', 20);
        $this->entity->setQuestion($longQuestion);
        $result = (string) $this->entity;
        self::assertStringEndsWith('...', $result);
        self::assertLessThanOrEqual(53, mb_strlen($result)); // 50 + '...'
    }

    /**
     * 测试ConversationType的getter和setter
     */
    public function testConversationType(): void
    {
        $this->entity->setConversationType(SiliconFlowConversation::TYPE_CONTINUOUS);
        self::assertSame(SiliconFlowConversation::TYPE_CONTINUOUS, $this->entity->getConversationType());

        $this->entity->setConversationType(SiliconFlowConversation::TYPE_SINGLE);
        self::assertSame(SiliconFlowConversation::TYPE_SINGLE, $this->entity->getConversationType());
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
     * 测试Question的getter和setter
     */
    public function testQuestion(): void
    {
        $question = '你好，请帮我解答一个问题';
        $this->entity->setQuestion($question);
        self::assertSame($question, $this->entity->getQuestion());
    }

    /**
     * 测试Answer的getter和setter
     */
    public function testAnswer(): void
    {
        $answer = '当然可以，我很乐意帮助您';
        $this->entity->setAnswer($answer);
        self::assertSame($answer, $this->entity->getAnswer());

        $this->entity->setAnswer(null);
        self::assertNull($this->entity->getAnswer());
    }

    /**
     * 测试Sender的getter和setter
     */
    public function testSender(): void
    {
        $this->entity->setSender($this->mockUser);
        self::assertSame($this->mockUser, $this->entity->getSender());
    }

    /**
     * 测试Context的getter和setter
     */
    public function testContext(): void
    {
        $contextConversation = new SiliconFlowConversation();
        $this->entity->setContext($contextConversation);
        self::assertSame($contextConversation, $this->entity->getContext());

        $this->entity->setContext(null);
        self::assertNull($this->entity->getContext());
    }

    /**
     * 测试ContextSnapshot的getter和setter
     */
    public function testContextSnapshot(): void
    {
        $snapshot = '之前的对话历史快照';
        $this->entity->setContextSnapshot($snapshot);
        self::assertSame($snapshot, $this->entity->getContextSnapshot());

        $this->entity->setContextSnapshot(null);
        self::assertNull($this->entity->getContextSnapshot());
    }

    /**
     * 测试完整的实体数据设置
     */
    public function testCompleteEntity(): void
    {
        $contextConversation = new SiliconFlowConversation();

        $this->entity->setConversationType(SiliconFlowConversation::TYPE_CONTINUOUS);
        $this->entity->setModel('gpt-4');
        $this->entity->setQuestion('这是一个测试问题');
        $this->entity->setAnswer('这是一个测试回答');
        $this->entity->setSender($this->mockUser);
        $this->entity->setContext($contextConversation);
        $this->entity->setContextSnapshot('历史快照');

        self::assertSame(SiliconFlowConversation::TYPE_CONTINUOUS, $this->entity->getConversationType());
        self::assertSame('gpt-4', $this->entity->getModel());
        self::assertSame('这是一个测试问题', $this->entity->getQuestion());
        self::assertSame('这是一个测试回答', $this->entity->getAnswer());
        self::assertSame($this->mockUser, $this->entity->getSender());
        self::assertSame($contextConversation, $this->entity->getContext());
        self::assertSame('历史快照', $this->entity->getContextSnapshot());
    }
}