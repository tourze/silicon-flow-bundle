<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Entity;

use BizUserBundle\Entity\BizUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowImageGeneration;

/**
 * SiliconFlow 图片生成实体测试
 */
#[CoversClass(SiliconFlowImageGeneration::class)]
#[RunTestsInSeparateProcesses]
class SiliconFlowImageGenerationTest extends AbstractIntegrationTestCase
{
    private SiliconFlowImageGeneration $entity;
    private BizUser $mockUser;

    protected function onSetUp(): void
    {
        $this->entity = new SiliconFlowImageGeneration();
        $this->mockUser = $this->createMock(BizUser::class);
    }

    /**
     * 测试实体实例化
     */
    public function testInstantiation(): void
    {
        self::assertInstanceOf(SiliconFlowImageGeneration::class, $this->entity);
        self::assertNull($this->entity->getId());
        self::assertSame(1, $this->entity->getBatchSize()); // 默认批量数量为1
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $this->entity->setModel('stable-diffusion');
        $this->entity->setPrompt('A beautiful sunset over the mountains');

        $result = (string) $this->entity;
        self::assertStringStartsWith('stable-diffusion: ', $result);
        self::assertStringContainsString('A beautiful sunset over the mountains', $result);

        // 测试长提示词截断
        $longPrompt = str_repeat('very long prompt text ', 10);
        $this->entity->setPrompt($longPrompt);
        $result = (string) $this->entity;
        self::assertStringEndsWith('...', $result);
    }

    /**
     * 测试Model的getter和setter
     */
    public function testModel(): void
    {
        $model = 'stable-diffusion-xl';
        $this->entity->setModel($model);
        self::assertSame($model, $this->entity->getModel());
    }

    /**
     * 测试Prompt的getter和setter
     */
    public function testPrompt(): void
    {
        $prompt = 'A cat sitting on a chair';
        $this->entity->setPrompt($prompt);
        self::assertSame($prompt, $this->entity->getPrompt());
    }

    /**
     * 测试NegativePrompt的getter和setter
     */
    public function testNegativePrompt(): void
    {
        $negativePrompt = 'blurry, low quality';
        $this->entity->setNegativePrompt($negativePrompt);
        self::assertSame($negativePrompt, $this->entity->getNegativePrompt());

        $this->entity->setNegativePrompt(null);
        self::assertNull($this->entity->getNegativePrompt());
    }

    /**
     * 测试ImageSize的getter和setter
     */
    public function testImageSize(): void
    {
        $size = '512x512';
        $this->entity->setImageSize($size);
        self::assertSame($size, $this->entity->getImageSize());

        $this->entity->setImageSize(null);
        self::assertNull($this->entity->getImageSize());
    }

    /**
     * 测试BatchSize的getter和setter
     */
    public function testBatchSize(): void
    {
        $batchSize = 4;
        $this->entity->setBatchSize($batchSize);
        self::assertSame($batchSize, $this->entity->getBatchSize());
    }

    /**
     * 测试Seed的getter和setter
     */
    public function testSeed(): void
    {
        $seed = 123456789;
        $this->entity->setSeed($seed);
        self::assertSame($seed, $this->entity->getSeed());

        $this->entity->setSeed(null);
        self::assertNull($this->entity->getSeed());
    }

    /**
     * 测试NumInferenceSteps的getter和setter
     */
    public function testNumInferenceSteps(): void
    {
        $steps = 50;
        $this->entity->setNumInferenceSteps($steps);
        self::assertSame($steps, $this->entity->getNumInferenceSteps());

        $this->entity->setNumInferenceSteps(null);
        self::assertNull($this->entity->getNumInferenceSteps());
    }

    /**
     * 测试RequestPayload的getter和setter
     */
    public function testRequestPayload(): void
    {
        $payload = ['prompt' => 'test', 'model' => 'test-model'];
        $this->entity->setRequestPayload($payload);
        self::assertSame($payload, $this->entity->getRequestPayload());
    }

    /**
     * 测试ResponsePayload的getter和setter
     */
    public function testResponsePayload(): void
    {
        $payload = ['images' => ['url1', 'url2']];
        $this->entity->setResponsePayload($payload);
        self::assertSame($payload, $this->entity->getResponsePayload());

        $this->entity->setResponsePayload(null);
        self::assertNull($this->entity->getResponsePayload());
    }

    /**
     * 测试ImageUrls的getter和setter
     */
    public function testImageUrls(): void
    {
        $urls = 'https://example.com/image1.jpg,https://example.com/image2.jpg';
        $this->entity->setImageUrls($urls);
        self::assertSame($urls, $this->entity->getImageUrls());

        $this->entity->setImageUrls(null);
        self::assertNull($this->entity->getImageUrls());
    }

    /**
     * 测试InferenceTime的getter和setter
     */
    public function testInferenceTime(): void
    {
        $time = 5.5;
        $this->entity->setInferenceTime($time);
        self::assertSame($time, $this->entity->getInferenceTime());

        $this->entity->setInferenceTime(null);
        self::assertNull($this->entity->getInferenceTime());
    }

    /**
     * 测试ResponseSeed的getter和setter
     */
    public function testResponseSeed(): void
    {
        $seed = 987654321;
        $this->entity->setResponseSeed($seed);
        self::assertSame($seed, $this->entity->getResponseSeed());

        $this->entity->setResponseSeed(null);
        self::assertNull($this->entity->getResponseSeed());
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
     * 测试Status的getter和setter
     */
    public function testStatus(): void
    {
        $status = 'completed';
        $this->entity->setStatus($status);
        self::assertSame($status, $this->entity->getStatus());

        $this->entity->setStatus(null);
        self::assertNull($this->entity->getStatus());
    }

    /**
     * 测试完整的实体数据设置
     */
    public function testCompleteEntity(): void
    {
        $this->entity->setModel('stable-diffusion-xl');
        $this->entity->setPrompt('A beautiful landscape');
        $this->entity->setNegativePrompt('blurry');
        $this->entity->setImageSize('1024x1024');
        $this->entity->setBatchSize(2);
        $this->entity->setSeed(123456);
        $this->entity->setNumInferenceSteps(50);
        $this->entity->setRequestPayload(['test' => 'data']);
        $this->entity->setResponsePayload(['response' => 'data']);
        $this->entity->setImageUrls('https://example.com/image.jpg');
        $this->entity->setInferenceTime(10.5);
        $this->entity->setResponseSeed(654321);
        $this->entity->setSender($this->mockUser);
        $this->entity->setStatus('success');

        self::assertSame('stable-diffusion-xl', $this->entity->getModel());
        self::assertSame('A beautiful landscape', $this->entity->getPrompt());
        self::assertSame('blurry', $this->entity->getNegativePrompt());
        self::assertSame('1024x1024', $this->entity->getImageSize());
        self::assertSame(2, $this->entity->getBatchSize());
        self::assertSame(123456, $this->entity->getSeed());
        self::assertSame(50, $this->entity->getNumInferenceSteps());
        self::assertSame(['test' => 'data'], $this->entity->getRequestPayload());
        self::assertSame(['response' => 'data'], $this->entity->getResponsePayload());
        self::assertSame('https://example.com/image.jpg', $this->entity->getImageUrls());
        self::assertSame(10.5, $this->entity->getInferenceTime());
        self::assertSame(654321, $this->entity->getResponseSeed());
        self::assertSame($this->mockUser, $this->entity->getSender());
        self::assertSame('success', $this->entity->getStatus());
    }
}