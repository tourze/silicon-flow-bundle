<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowModel;

/**
 * SiliconFlow 模型实体测试
 */
#[CoversClass(SiliconFlowModel::class)]
#[RunTestsInSeparateProcesses]
class SiliconFlowModelTest extends AbstractIntegrationTestCase
{
    private SiliconFlowModel $entity;

    protected function onSetUp(): void
    {
        $this->entity = new SiliconFlowModel();
    }

    /**
     * 测试实体实例化
     */
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(SiliconFlowModel::class, $this->entity);
        $this->assertNull($this->entity->getId());
        $this->assertSame('', $this->entity->getModelId());
        $this->assertSame('model', $this->entity->getObjectType());
        $this->assertTrue($this->entity->isActive());
        $this->assertNull($this->entity->getMetadata());
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $this->entity->setModelId('claude-3-5-haiku-20241022');

        $result = (string) $this->entity;
        $this->assertSame('claude-3-5-haiku-20241022', $result);
    }

    /**
     * 测试modelId属性
     */
    public function testModelId(): void
    {
        $modelId = 'claude-3-5-sonnet-20241022';
        $this->entity->setModelId($modelId);
        $this->assertSame($modelId, $this->entity->getModelId());
    }

    /**
     * 测试objectType属性
     */
    public function testObjectType(): void
    {
        $objectType = 'chat.completion';
        $this->entity->setObjectType($objectType);
        $this->assertSame($objectType, $this->entity->getObjectType());
    }

    /**
     * 测试isActive属性
     */
    public function testIsActive(): void
    {
        // 默认为true
        $this->assertTrue($this->entity->isActive());

        // 设置为false
        $this->entity->setIsActive(false);
        $this->assertFalse($this->entity->isActive());

        // 设置为true
        $this->entity->setIsActive(true);
        $this->assertTrue($this->entity->isActive());
    }

    /**
     * 测试metadata属性
     */
    public function testMetadata(): void
    {
        $metadata = [
            'max_tokens' => 8192,
            'context_window' => 200000,
            'pricing' => ['input' => 0.003, 'output' => 0.015]
        ];

        $this->entity->setMetadata($metadata);
        $this->assertSame($metadata, $this->entity->getMetadata());

        // 测试null值
        $this->entity->setMetadata(null);
        $this->assertNull($this->entity->getMetadata());
    }

    /**
     * 测试TimestampableAware trait
     */
    public function testTimestampableTrait(): void
    {
        // 验证实体具有timestampable方法
        $this->assertTrue(method_exists($this->entity, 'getCreatedAt'));
        $this->assertTrue(method_exists($this->entity, 'getUpdatedAt'));
        $this->assertTrue(method_exists($this->entity, 'setCreatedAt'));
        $this->assertTrue(method_exists($this->entity, 'setUpdatedAt'));
    }

    /**
     * 测试Stringable接口
     */
    public function testStringableInterface(): void
    {
        $this->assertInstanceOf(\Stringable::class, $this->entity);
    }

    /**
     * 测试默认值
     */
    public function testDefaultValues(): void
    {
        $entity = new SiliconFlowModel();

        $this->assertSame('', $entity->getModelId());
        $this->assertSame('model', $entity->getObjectType());
        $this->assertTrue($entity->isActive());
        $this->assertNull($entity->getMetadata());
    }
}