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
        $this->assertInstanceOf(SiliconFlowImageGeneration::class, $this->entity);
        $this->assertNull($this->entity->getId());
        $this->assertSame(1, $this->entity->getBatchSize()); // 默认批量数量为1
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $this->entity->setModel('stable-diffusion');
        $this->entity->setPrompt('A beautiful sunset over the mountains');

        $result = (string) $this->entity;
        $this->assertStringStartsWith('stable-diffusion: ', $result);
        $this->assertStringContainsString('A beautiful sunset over the mountains', $result);

        // 测试长提示词截断
        $longPrompt = str_repeat('very long prompt text ', 10);
        $this->entity->setPrompt($longPrompt);
        $result = (string) $this->entity;
        $this->assertStringEndsWith('...', $result);
    }

    /**
     * 测试Model的getter和setter
     */
    public function testModel(): void
    {
        $model = 'stable-diffusion-xl';
        $this->entity->setModel($model);
        $this->assertSame($model, $this->entity->getModel());
    }

    /**
     * 测试Prompt的getter和setter
     */
    public function testPrompt(): void
    {
        $prompt = 'A cat sitting on a chair';
        $this->entity->setPrompt($prompt);
        $this->assertSame($prompt, $this->entity->getPrompt());
    }

    /**
     * 测试NegativePrompt的getter和setter
     */
    public function testNegativePrompt(): void
    {
        $negativePrompt = 'blurry, low quality';
        $this->entity->setNegativePrompt($negativePrompt);
        $this->assertSame($negativePrompt, $this->entity->getNegativePrompt());

        $this->entity->setNegativePrompt(null);
        $this->assertNull($this->entity->getNegativePrompt());
    }

    /**
     * 测试ImageSize的getter和setter
     */
    public function testImageSize(): void
    {
        $size = '512x512';
        $this->entity->setImageSize($size);
        $this->assertSame($size, $this->entity->getImageSize());

        $this->entity->setImageSize(null);
        $this->assertNull($this->entity->getImageSize());
    }

    /**
     * 测试BatchSize的getter和setter
     */
    public function testBatchSize(): void
    {
        $batchSize = 4;
        $this->entity->setBatchSize($batchSize);
        $this->assertSame($batchSize, $this->entity->getBatchSize());
    }

    /**
     * 测试Seed的getter和setter
     */
    public function testSeed(): void
    {
        $seed = 123456789;
        $this->entity->setSeed($seed);
        $this->assertSame($seed, $this->entity->getSeed());

        $this->entity->setSeed(null);
        $this->assertNull($this->entity->getSeed());
    }

    /**
     * 测试NumInferenceSteps的getter和setter
     */
    public function testNumInferenceSteps(): void
    {
        $steps = 50;
        $this->entity->setNumInferenceSteps($steps);
        $this->assertSame($steps, $this->entity->getNumInferenceSteps());

        $this->entity->setNumInferenceSteps(null);
        $this->assertNull($this->entity->getNumInferenceSteps());
    }

    /**
     * 测试RequestPayload的getter和setter
     */
    public function testRequestPayload(): void
    {
        $payload = ['prompt' => 'test', 'model' => 'test-model'];
        $this->entity->setRequestPayload($payload);
        $this->assertSame($payload, $this->entity->getRequestPayload());
    }

    /**
     * 测试ResponsePayload的getter和setter
     */
    public function testResponsePayload(): void
    {
        $payload = ['images' => ['url1', 'url2']];
        $this->entity->setResponsePayload($payload);
        $this->assertSame($payload, $this->entity->getResponsePayload());

        $this->entity->setResponsePayload(null);
        $this->assertNull($this->entity->getResponsePayload());
    }

    /**
     * 测试ImageUrls的getter和setter
     */
    public function testImageUrls(): void
    {
        $urls = 'https://example.com/image1.jpg,https://example.com/image2.jpg';
        $this->entity->setImageUrls($urls);
        $this->assertSame($urls, $this->entity->getImageUrls());

        $this->entity->setImageUrls(null);
        $this->assertNull($this->entity->getImageUrls());
    }

    /**
     * 测试InferenceTime的getter和setter
     */
    public function testInferenceTime(): void
    {
        $time = 5.5;
        $this->entity->setInferenceTime($time);
        $this->assertSame($time, $this->entity->getInferenceTime());

        $this->entity->setInferenceTime(null);
        $this->assertNull($this->entity->getInferenceTime());
    }

    /**
     * 测试ResponseSeed的getter和setter
     */
    public function testResponseSeed(): void
    {
        $seed = 987654321;
        $this->entity->setResponseSeed($seed);
        $this->assertSame($seed, $this->entity->getResponseSeed());

        $this->entity->setResponseSeed(null);
        $this->assertNull($this->entity->getResponseSeed());
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
     * 测试Status的getter和setter
     */
    public function testStatus(): void
    {
        $status = 'completed';
        $this->entity->setStatus($status);
        $this->assertSame($status, $this->entity->getStatus());

        $this->entity->setStatus(null);
        $this->assertNull($this->entity->getStatus());
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

        $this->assertSame('stable-diffusion-xl', $this->entity->getModel());
        $this->assertSame('A beautiful landscape', $this->entity->getPrompt());
        $this->assertSame('blurry', $this->entity->getNegativePrompt());
        $this->assertSame('1024x1024', $this->entity->getImageSize());
        $this->assertSame(2, $this->entity->getBatchSize());
        $this->assertSame(123456, $this->entity->getSeed());
        $this->assertSame(50, $this->entity->getNumInferenceSteps());
        $this->assertSame(['test' => 'data'], $this->entity->getRequestPayload());
        $this->assertSame(['response' => 'data'], $this->entity->getResponsePayload());
        $this->assertSame('https://example.com/image.jpg', $this->entity->getImageUrls());
        $this->assertSame(10.5, $this->entity->getInferenceTime());
        $this->assertSame(654321, $this->entity->getResponseSeed());
        $this->assertSame($this->mockUser, $this->entity->getSender());
        $this->assertSame('success', $this->entity->getStatus());
    }
}