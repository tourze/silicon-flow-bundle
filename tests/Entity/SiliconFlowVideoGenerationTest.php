<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Entity;

use BizUserBundle\Entity\BizUser;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowVideoGeneration;

/**
 * SiliconFlow 视频生成实体测试
 */
#[CoversClass(SiliconFlowVideoGeneration::class)]
class SiliconFlowVideoGenerationTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new SiliconFlowVideoGeneration();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'requestId' => ['requestId', 'req_123456789'],
            'model' => ['model', 'cogvideox-5b'],
            'prompt' => ['prompt', 'A beautiful sunset over the ocean with waves crashing'],
            'negativePrompt' => ['negativePrompt', 'blurry, low quality, distorted'],
            'image' => ['image', 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAA...'],
            'imageSize' => ['imageSize', '768x768'],
            'seed' => ['seed', 123456789],
            'numInferenceSteps' => ['numInferenceSteps', 20],
            'requestPayload' => ['requestPayload', ['test' => 'data']],
            'status' => ['status', 'completed'],
        ];
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $entity = $this->createEntity();
        $entity->setModel('cogvideox-flash');
        $entity->setPrompt('A flying bird in the sky');

        $result = (string) $entity;
        self::assertStringStartsWith('cogvideox-flash: ', $result);
        self::assertStringContainsString('A flying bird in the sky', $result);

        // 测试长提示词截断
        $longPrompt = str_repeat('very long video prompt text ', 10);
        $entity->setPrompt($longPrompt);

        $result = (string) $entity;
        self::assertStringContainsString('...', $result);
        self::assertLessThanOrEqual(100, strlen($result));
    }

    /**
     * 测试model属性
     */
    public function testModel(): void
    {
        $entity = $this->createEntity();
        $model = 'cogvideox-5b';
        $entity->setModel($model);
        self::assertSame($model, $entity->getModel());
    }

    /**
     * 测试prompt属性
     */
    public function testPrompt(): void
    {
        $entity = $this->createEntity();
        $prompt = 'A beautiful sunset over the ocean with waves crashing';
        $entity->setPrompt($prompt);
        self::assertSame($prompt, $entity->getPrompt());
    }

    /**
     * 测试negativePrompt属性
     */
    public function testNegativePrompt(): void
    {
        $entity = $this->createEntity();
        $negativePrompt = 'blurry, low quality, distorted';
        $entity->setNegativePrompt($negativePrompt);
        self::assertSame($negativePrompt, $entity->getNegativePrompt());

        $entity->setNegativePrompt(null);
        self::assertNull($entity->getNegativePrompt());
    }

    /**
     * 测试image属性
     */
    public function testImage(): void
    {
        $entity = $this->createEntity();
        $imageBase64 = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAA...';
        $entity->setImage($imageBase64);
        self::assertSame($imageBase64, $entity->getImage());

        $entity->setImage(null);
        self::assertNull($entity->getImage());
    }

    /**
     * 测试imageSize属性
     */
    public function testImageSize(): void
    {
        $entity = $this->createEntity();
        $imageSize = '768x768';
        $entity->setImageSize($imageSize);
        self::assertSame($imageSize, $entity->getImageSize());

        $entity->setImageSize(null);
        self::assertNull($entity->getImageSize());
    }

    /**
     * 测试numInferenceSteps属性
     */
    public function testNumInferenceSteps(): void
    {
        $entity = $this->createEntity();
        $steps = 20;
        $entity->setNumInferenceSteps($steps);
        self::assertSame($steps, $entity->getNumInferenceSteps());
    }

    /**
     * 测试status属性
     */
    public function testStatus(): void
    {
        $entity = $this->createEntity();
        $status = 'completed';
        $entity->setStatus($status);
        self::assertSame($status, $entity->getStatus());
    }

    /**
     * 测试user关联
     */
    public function testUser(): void
    {
        $entity = $this->createEntity();
        $mockUser = $this->createMock(BizUser::class);
        $entity->setUser($mockUser);
        self::assertSame($mockUser, $entity->getUser());
    }

    /**
     * 测试requestId属性
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
     * 测试TimestampableAware trait
     */
    public function testTimestampableTrait(): void
    {
        $entity = $this->createEntity();
        self::assertTrue(method_exists($entity, 'getCreateTime'));
        self::assertTrue(method_exists($entity, 'getUpdateTime'));
        self::assertTrue(method_exists($entity, 'setCreateTime'));
        self::assertTrue(method_exists($entity, 'setUpdateTime'));
    }

    /**
     * 测试Stringable接口
     */
    public function testStringableInterface(): void
    {
        $entity = $this->createEntity();
        self::assertInstanceOf(\Stringable::class, $entity);
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
        self::assertNull($entity->getStatus());
        self::assertNull($entity->getRequestId());
        self::assertNull($entity->getUser());
    }
}