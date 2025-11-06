<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Entity;

use BizUserBundle\Entity\BizUser;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowImageGeneration;

/**
 * SiliconFlow 图片生成实体测试
 */
#[CoversClass(SiliconFlowImageGeneration::class)]
class SiliconFlowImageGenerationTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new SiliconFlowImageGeneration();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'model' => ['model', 'stable-diffusion-xl'],
            'prompt' => ['prompt', 'A beautiful landscape'],
            'negativePrompt' => ['negativePrompt', 'blurry'],
            'imageSize' => ['imageSize', '1024x1024'],
            'batchSize' => ['batchSize', 2],
            'seed' => ['seed', 123456],
            'numInferenceSteps' => ['numInferenceSteps', 50],
            'requestPayload' => ['requestPayload', ['test' => 'data']],
            'responsePayload' => ['responsePayload', ['response' => 'data']],
            'imageUrls' => ['imageUrls', 'https://example.com/image.jpg'],
            'inferenceTime' => ['inferenceTime', 10.5],
            'responseSeed' => ['responseSeed', 654321],
            'status' => ['status', 'success'],
        ];
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $entity = $this->createEntity();
        $entity->setModel('stable-diffusion');
        $entity->setPrompt('A beautiful sunset over the mountains');

        $result = (string) $entity;
        self::assertStringStartsWith('stable-diffusion: ', $result);
        self::assertStringContainsString('A beautiful sunset over the mountains', $result);

        // 测试长提示词截断
        $longPrompt = str_repeat('very long prompt text ', 10);
        $entity->setPrompt($longPrompt);
        $result = (string) $entity;
        self::assertStringEndsWith('...', $result);
    }

    /**
     * 测试Model的getter和setter
     */
    public function testModel(): void
    {
        $entity = $this->createEntity();
        $model = 'stable-diffusion-xl';
        $entity->setModel($model);
        self::assertSame($model, $entity->getModel());
    }

    /**
     * 测试Prompt的getter和setter
     */
    public function testPrompt(): void
    {
        $entity = $this->createEntity();
        $prompt = 'A cat sitting on a chair';
        $entity->setPrompt($prompt);
        self::assertSame($prompt, $entity->getPrompt());
    }

    /**
     * 测试NegativePrompt的getter和setter
     */
    public function testNegativePrompt(): void
    {
        $entity = $this->createEntity();
        $negativePrompt = 'blurry, low quality';
        $entity->setNegativePrompt($negativePrompt);
        self::assertSame($negativePrompt, $entity->getNegativePrompt());

        $entity->setNegativePrompt(null);
        self::assertNull($entity->getNegativePrompt());
    }

    /**
     * 测试ImageSize的getter和setter
     */
    public function testImageSize(): void
    {
        $entity = $this->createEntity();
        $size = '512x512';
        $entity->setImageSize($size);
        self::assertSame($size, $entity->getImageSize());

        $entity->setImageSize(null);
        self::assertNull($entity->getImageSize());
    }

    /**
     * 测试BatchSize的getter和setter
     */
    public function testBatchSize(): void
    {
        $entity = $this->createEntity();
        $batchSize = 4;
        $entity->setBatchSize($batchSize);
        self::assertSame($batchSize, $entity->getBatchSize());
    }

    /**
     * 测试Seed的getter和setter
     */
    public function testSeed(): void
    {
        $entity = $this->createEntity();
        $seed = 123456789;
        $entity->setSeed($seed);
        self::assertSame($seed, $entity->getSeed());

        $entity->setSeed(null);
        self::assertNull($entity->getSeed());
    }

    /**
     * 测试NumInferenceSteps的getter和setter
     */
    public function testNumInferenceSteps(): void
    {
        $entity = $this->createEntity();
        $steps = 50;
        $entity->setNumInferenceSteps($steps);
        self::assertSame($steps, $entity->getNumInferenceSteps());

        $entity->setNumInferenceSteps(null);
        self::assertNull($entity->getNumInferenceSteps());
    }

    /**
     * 测试RequestPayload的getter和setter
     */
    public function testRequestPayload(): void
    {
        $entity = $this->createEntity();
        $payload = ['prompt' => 'test', 'model' => 'test-model'];
        $entity->setRequestPayload($payload);
        self::assertSame($payload, $entity->getRequestPayload());
    }

    /**
     * 测试ResponsePayload的getter和setter
     */
    public function testResponsePayload(): void
    {
        $entity = $this->createEntity();
        $payload = ['images' => ['url1', 'url2']];
        $entity->setResponsePayload($payload);
        self::assertSame($payload, $entity->getResponsePayload());

        $entity->setResponsePayload(null);
        self::assertNull($entity->getResponsePayload());
    }

    /**
     * 测试ImageUrls的getter和setter
     */
    public function testImageUrls(): void
    {
        $entity = $this->createEntity();
        $urls = 'https://example.com/image1.jpg,https://example.com/image2.jpg';
        $entity->setImageUrls($urls);
        self::assertSame($urls, $entity->getImageUrls());

        $entity->setImageUrls(null);
        self::assertNull($entity->getImageUrls());
    }

    /**
     * 测试InferenceTime的getter和setter
     */
    public function testInferenceTime(): void
    {
        $entity = $this->createEntity();
        $time = 5.5;
        $entity->setInferenceTime($time);
        self::assertSame($time, $entity->getInferenceTime());

        $entity->setInferenceTime(null);
        self::assertNull($entity->getInferenceTime());
    }

    /**
     * 测试ResponseSeed的getter和setter
     */
    public function testResponseSeed(): void
    {
        $entity = $this->createEntity();
        $seed = 987654321;
        $entity->setResponseSeed($seed);
        self::assertSame($seed, $entity->getResponseSeed());

        $entity->setResponseSeed(null);
        self::assertNull($entity->getResponseSeed());
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
     * 测试Status的getter和setter
     */
    public function testStatus(): void
    {
        $entity = $this->createEntity();
        $status = 'completed';
        $entity->setStatus($status);
        self::assertSame($status, $entity->getStatus());

        $entity->setStatus(null);
        self::assertNull($entity->getStatus());
    }

    /**
     * 测试完整的实体数据设置
     */
    public function testCompleteEntity(): void
    {
        $entity = $this->createEntity();
        $mockUser = $this->createMock(BizUser::class);

        $entity->setModel('stable-diffusion-xl');
        $entity->setPrompt('A beautiful landscape');
        $entity->setNegativePrompt('blurry');
        $entity->setImageSize('1024x1024');
        $entity->setBatchSize(2);
        $entity->setSeed(123456);
        $entity->setNumInferenceSteps(50);
        $entity->setRequestPayload(['test' => 'data']);
        $entity->setResponsePayload(['response' => 'data']);
        $entity->setImageUrls('https://example.com/image.jpg');
        $entity->setInferenceTime(10.5);
        $entity->setResponseSeed(654321);
        $entity->setSender($mockUser);
        $entity->setStatus('success');

        self::assertSame('stable-diffusion-xl', $entity->getModel());
        self::assertSame('A beautiful landscape', $entity->getPrompt());
        self::assertSame('blurry', $entity->getNegativePrompt());
        self::assertSame('1024x1024', $entity->getImageSize());
        self::assertSame(2, $entity->getBatchSize());
        self::assertSame(123456, $entity->getSeed());
        self::assertSame(50, $entity->getNumInferenceSteps());
        self::assertSame(['test' => 'data'], $entity->getRequestPayload());
        self::assertSame(['response' => 'data'], $entity->getResponsePayload());
        self::assertSame('https://example.com/image.jpg', $entity->getImageUrls());
        self::assertSame(10.5, $entity->getInferenceTime());
        self::assertSame(654321, $entity->getResponseSeed());
        self::assertSame($mockUser, $entity->getSender());
        self::assertSame('success', $entity->getStatus());
    }
}