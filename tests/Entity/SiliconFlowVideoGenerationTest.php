<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Entity;

use BizUserBundle\Entity\BizUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowVideoGeneration;

/**
 * SiliconFlow 视频生成实体测试
 */
#[CoversClass(SiliconFlowVideoGeneration::class)]
#[RunTestsInSeparateProcesses]
class SiliconFlowVideoGenerationTest extends AbstractIntegrationTestCase
{
    private SiliconFlowVideoGeneration $entity;
    private BizUser $mockUser;

    protected function onSetUp(): void
    {
        $this->entity = new SiliconFlowVideoGeneration();
        $this->mockUser = $this->createMock(BizUser::class);
    }

    /**
     * 测试实体实例化
     */
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(SiliconFlowVideoGeneration::class, $this->entity);
        $this->assertNull($this->entity->getId());
        $this->assertSame('', $this->entity->getModel());
        $this->assertSame('', $this->entity->getPrompt());
        $this->assertSame('pending', $this->entity->getStatus());
        $this->assertSame(5, $this->entity->getNumInferenceSteps());
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $this->entity->setModel('cogvideox-flash');
        $this->entity->setPrompt('A flying bird in the sky');

        $result = (string) $this->entity;
        $this->assertStringStartsWith('cogvideox-flash: ', $result);
        $this->assertStringContainsString('A flying bird in the sky', $result);

        // 测试长提示词截断
        $longPrompt = str_repeat('very long video prompt text ', 10);
        $this->entity->setPrompt($longPrompt);

        $result = (string) $this->entity;
        $this->assertStringContainsString('...', $result);
        $this->assertLessThanOrEqual(100, strlen($result));
    }

    /**
     * 测试model属性
     */
    public function testModel(): void
    {
        $model = 'cogvideox-5b';
        $this->entity->setModel($model);
        $this->assertSame($model, $this->entity->getModel());
    }

    /**
     * 测试prompt属性
     */
    public function testPrompt(): void
    {
        $prompt = 'A beautiful sunset over the ocean with waves crashing';
        $this->entity->setPrompt($prompt);
        $this->assertSame($prompt, $this->entity->getPrompt());
    }

    /**
     * 测试negativePrompt属性
     */
    public function testNegativePrompt(): void
    {
        $negativePrompt = 'blurry, low quality, distorted';
        $this->entity->setNegativePrompt($negativePrompt);
        $this->assertSame($negativePrompt, $this->entity->getNegativePrompt());

        $this->entity->setNegativePrompt(null);
        $this->assertNull($this->entity->getNegativePrompt());
    }

    /**
     * 测试image属性
     */
    public function testImage(): void
    {
        $imageBase64 = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAA...';
        $this->entity->setImage($imageBase64);
        $this->assertSame($imageBase64, $this->entity->getImage());

        $this->entity->setImage(null);
        $this->assertNull($this->entity->getImage());
    }

    /**
     * 测试imageSize属性
     */
    public function testImageSize(): void
    {
        $imageSize = '768x768';
        $this->entity->setImageSize($imageSize);
        $this->assertSame($imageSize, $this->entity->getImageSize());

        $this->entity->setImageSize(null);
        $this->assertNull($this->entity->getImageSize());
    }

    /**
     * 测试numInferenceSteps属性
     */
    public function testNumInferenceSteps(): void
    {
        $steps = 20;
        $this->entity->setNumInferenceSteps($steps);
        $this->assertSame($steps, $this->entity->getNumInferenceSteps());
    }

    /**
     * 测试status属性
     */
    public function testStatus(): void
    {
        $status = 'completed';
        $this->entity->setStatus($status);
        $this->assertSame($status, $this->entity->getStatus());
    }

    /**
     * 测试user关联
     */
    public function testUser(): void
    {
        $this->entity->setUser($this->mockUser);
        $this->assertSame($this->mockUser, $this->entity->getUser());
    }

    /**
     * 测试requestId属性
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
     * 测试TimestampableAware trait
     */
    public function testTimestampableTrait(): void
    {
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
        $entity = new SiliconFlowVideoGeneration();

        $this->assertSame('', $entity->getModel());
        $this->assertSame('', $entity->getPrompt());
        $this->assertNull($entity->getNegativePrompt());
        $this->assertNull($entity->getImage());
        $this->assertNull($entity->getImageSize());
        $this->assertSame(5, $entity->getNumInferenceSteps());
        $this->assertSame('pending', $entity->getStatus());
        $this->assertNull($entity->getRequestId());
        $this->assertNull($entity->getUser());
    }
}