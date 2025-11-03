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
        self::assertInstanceOf(SiliconFlowVideoGeneration::class, $this->entity);
        self::assertNull($this->entity->getId());
        self::assertSame('', $this->entity->getModel());
        self::assertSame('', $this->entity->getPrompt());
        self::assertSame('pending', $this->entity->getStatus());
        self::assertSame(5, $this->entity->getNumInferenceSteps());
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $this->entity->setModel('cogvideox-flash');
        $this->entity->setPrompt('A flying bird in the sky');

        $result = (string) $this->entity;
        self::assertStringStartsWith('cogvideox-flash: ', $result);
        self::assertStringContainsString('A flying bird in the sky', $result);

        // 测试长提示词截断
        $longPrompt = str_repeat('very long video prompt text ', 10);
        $this->entity->setPrompt($longPrompt);

        $result = (string) $this->entity;
        self::assertStringContainsString('...', $result);
        self::assertLessThanOrEqual(100, strlen($result));
    }

    /**
     * 测试model属性
     */
    public function testModel(): void
    {
        $model = 'cogvideox-5b';
        $this->entity->setModel($model);
        self::assertSame($model, $this->entity->getModel());
    }

    /**
     * 测试prompt属性
     */
    public function testPrompt(): void
    {
        $prompt = 'A beautiful sunset over the ocean with waves crashing';
        $this->entity->setPrompt($prompt);
        self::assertSame($prompt, $this->entity->getPrompt());
    }

    /**
     * 测试negativePrompt属性
     */
    public function testNegativePrompt(): void
    {
        $negativePrompt = 'blurry, low quality, distorted';
        $this->entity->setNegativePrompt($negativePrompt);
        self::assertSame($negativePrompt, $this->entity->getNegativePrompt());

        $this->entity->setNegativePrompt(null);
        self::assertNull($this->entity->getNegativePrompt());
    }

    /**
     * 测试image属性
     */
    public function testImage(): void
    {
        $imageBase64 = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAA...';
        $this->entity->setImage($imageBase64);
        self::assertSame($imageBase64, $this->entity->getImage());

        $this->entity->setImage(null);
        self::assertNull($this->entity->getImage());
    }

    /**
     * 测试imageSize属性
     */
    public function testImageSize(): void
    {
        $imageSize = '768x768';
        $this->entity->setImageSize($imageSize);
        self::assertSame($imageSize, $this->entity->getImageSize());

        $this->entity->setImageSize(null);
        self::assertNull($this->entity->getImageSize());
    }

    /**
     * 测试numInferenceSteps属性
     */
    public function testNumInferenceSteps(): void
    {
        $steps = 20;
        $this->entity->setNumInferenceSteps($steps);
        self::assertSame($steps, $this->entity->getNumInferenceSteps());
    }

    /**
     * 测试status属性
     */
    public function testStatus(): void
    {
        $status = 'completed';
        $this->entity->setStatus($status);
        self::assertSame($status, $this->entity->getStatus());
    }

    /**
     * 测试user关联
     */
    public function testUser(): void
    {
        $this->entity->setUser($this->mockUser);
        self::assertSame($this->mockUser, $this->entity->getUser());
    }

    /**
     * 测试requestId属性
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
     * 测试TimestampableAware trait
     */
    public function testTimestampableTrait(): void
    {
        self::assertTrue(method_exists($this->entity, 'getCreatedAt'));
        self::assertTrue(method_exists($this->entity, 'getUpdatedAt'));
        self::assertTrue(method_exists($this->entity, 'setCreatedAt'));
        self::assertTrue(method_exists($this->entity, 'setUpdatedAt'));
    }

    /**
     * 测试Stringable接口
     */
    public function testStringableInterface(): void
    {
        self::assertInstanceOf(\Stringable::class, $this->entity);
    }

    /**
     * 测试默认值
     */
    public function testDefaultValues(): void
    {
        $entity = new SiliconFlowVideoGeneration();

        self::assertSame('', $entity->getModel());
        self::assertSame('', $entity->getPrompt());
        self::assertNull($entity->getNegativePrompt());
        self::assertNull($entity->getImage());
        self::assertNull($entity->getImageSize());
        self::assertSame(5, $entity->getNumInferenceSteps());
        self::assertSame('pending', $entity->getStatus());
        self::assertNull($entity->getRequestId());
        self::assertNull($entity->getUser());
    }
}