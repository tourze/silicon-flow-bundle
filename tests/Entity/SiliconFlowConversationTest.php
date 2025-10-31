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
        $this->assertInstanceOf(SiliconFlowConversation::class, $this->entity);
        $this->assertNull($this->entity->getId());
        $this->assertSame(SiliconFlowConversation::TYPE_SINGLE, $this->entity->getConversationType());
    }

    /**
     * 测试常量定义
     */
    public function testConstants(): void
    {
        $this->assertSame('single', SiliconFlowConversation::TYPE_SINGLE);
        $this->assertSame('continuous', SiliconFlowConversation::TYPE_CONTINUOUS);
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $shortQuestion = 'Hello World';
        $this->entity->setQuestion($shortQuestion);
        $this->assertSame($shortQuestion, (string) $this->entity);

        // 测试长文本截断
        $longQuestion = str_repeat('这是一个非常长的问题，', 20);
        $this->entity->setQuestion($longQuestion);
        $result = (string) $this->entity;
        $this->assertStringEndsWith('...', $result);
        $this->assertLessThanOrEqual(53, mb_strlen($result)); // 50 + '...'
    }

    /**
     * 测试ConversationType的getter和setter
     */
    public function testConversationType(): void
    {
        $this->entity->setConversationType(SiliconFlowConversation::TYPE_CONTINUOUS);
        $this->assertSame(SiliconFlowConversation::TYPE_CONTINUOUS, $this->entity->getConversationType());

        $this->entity->setConversationType(SiliconFlowConversation::TYPE_SINGLE);
        $this->assertSame(SiliconFlowConversation::TYPE_SINGLE, $this->entity->getConversationType());
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
     * 测试Question的getter和setter
     */
    public function testQuestion(): void
    {
        $question = '你好，请帮我解答一个问题';
        $this->entity->setQuestion($question);
        $this->assertSame($question, $this->entity->getQuestion());
    }

    /**
     * 测试Answer的getter和setter
     */
    public function testAnswer(): void
    {
        $answer = '当然可以，我很乐意帮助您';
        $this->entity->setAnswer($answer);
        $this->assertSame($answer, $this->entity->getAnswer());

        $this->entity->setAnswer(null);
        $this->assertNull($this->entity->getAnswer());
    }

    /**
     * 测试Sender的getter和setter
     */
    public function testSender(): void
    {
        $this->entity->setSender($this->mockUser);
        $this->assertSame($this->mockUser, $this->entity->getSender());
    }

    /**
     * 测试Context的getter和setter
     */
    public function testContext(): void
    {
        $contextConversation = new SiliconFlowConversation();
        $this->entity->setContext($contextConversation);
        $this->assertSame($contextConversation, $this->entity->getContext());

        $this->entity->setContext(null);
        $this->assertNull($this->entity->getContext());
    }

    /**
     * 测试ContextSnapshot的getter和setter
     */
    public function testContextSnapshot(): void
    {
        $snapshot = '之前的对话历史快照';
        $this->entity->setContextSnapshot($snapshot);
        $this->assertSame($snapshot, $this->entity->getContextSnapshot());

        $this->entity->setContextSnapshot(null);
        $this->assertNull($this->entity->getContextSnapshot());
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

        $this->assertSame(SiliconFlowConversation::TYPE_CONTINUOUS, $this->entity->getConversationType());
        $this->assertSame('gpt-4', $this->entity->getModel());
        $this->assertSame('这是一个测试问题', $this->entity->getQuestion());
        $this->assertSame('这是一个测试回答', $this->entity->getAnswer());
        $this->assertSame($this->mockUser, $this->entity->getSender());
        $this->assertSame($contextConversation, $this->entity->getContext());
        $this->assertSame('历史快照', $this->entity->getContextSnapshot());
    }
}