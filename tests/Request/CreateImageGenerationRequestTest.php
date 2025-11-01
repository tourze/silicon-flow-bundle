<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Exception\ApiException;
use Tourze\SiliconFlowBundle\Request\CreateImageGenerationRequest;

#[CoversClass(CreateImageGenerationRequest::class)]
final class CreateImageGenerationRequestTest extends RequestTestCase
{
    public function testBuildRequestOptions(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('image');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('image-token');

        $request = new CreateImageGenerationRequest(
            $config,
            'Kwai-Kolors/Kolors',
            'an island near the sea',
            negativePrompt: 'low quality',
            imageSize: '1024x1024',
            batchSize: 2,
            seed: 42,
            numInferenceSteps: 30,
            extraOptions: ['temperature' => 0.7],
        );

        $payload = $request->toArray();
        self::assertSame('Kwai-Kolors/Kolors', $payload['model']);
        self::assertSame('an island near the sea', $payload['prompt']);
        self::assertSame('low quality', $payload['negative_prompt']);
        self::assertSame('1024x1024', $payload['image_size']);
        self::assertSame(2, $payload['batch_size']);
        self::assertSame(42, $payload['seed']);
        self::assertSame(30, $payload['num_inference_steps']);
        self::assertSame(0.7, $payload['temperature']);

        $options = $request->getRequestOptions();
        self::assertArrayHasKey('headers', $options);
        $headers = $options['headers'];
        self::assertIsArray($headers);

        self::assertSame('Bearer image-token', $headers['Authorization'] ?? null);
        self::assertSame('application/json', $headers['Content-Type'] ?? null);
        self::assertArrayHasKey('timeout', $options);
    }

    public function testValidateFailsForInvalidBatchSize(): void
    {
        $this->expectException(ApiException::class);

        $config = new SiliconFlowConfig();
        $config->setName('invalid');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('token');

        $request = new CreateImageGenerationRequest(
            $config,
            'Kwai-Kolors/Kolors',
            'prompt',
            batchSize: 10,
        );

        $request->validate();
    }

    public function testValidateFailsForEmptyPrompt(): void
    {
        $this->expectException(ApiException::class);

        $config = new SiliconFlowConfig();
        $config->setName('invalid');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('token');

        $request = new CreateImageGenerationRequest(
            $config,
            'Kwai-Kolors/Kolors',
            '',
            imageSize: '100x100',
        );

        $request->validate();
    }

    public function testParseResponse(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('test');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $request = new CreateImageGenerationRequest(
            $config,
            'Kwai-Kolors/Kolors',
            'a beautiful landscape',
            negativePrompt: 'low quality',
            imageSize: '1024x1024',
            batchSize: 2,
            seed: 42,
            numInferenceSteps: 30,
        );

        $response = json_encode([
            'images' => [
                'https://example.com/image1.jpg',
                'https://example.com/image2.jpg'
            ],
            'created' => time(),
            'model' => 'Kwai-Kolors/Kolors'
        ]);
        if ($response === false) {
            throw new \RuntimeException('Failed to encode test JSON response');
        }

        $result = $request->parseResponse($response);

        self::assertArrayHasKey('images', $result);
        $images = $result['images'];
        self::assertIsArray($images);
        self::assertCount(2, $images);
        self::assertArrayHasKey(0, $images);
        self::assertArrayHasKey(1, $images);
        self::assertSame('https://example.com/image1.jpg', $images[0]);
        self::assertSame('https://example.com/image2.jpg', $images[1]);
        self::assertArrayHasKey('model', $result);
        self::assertSame('Kwai-Kolors/Kolors', $result['model']);
    }

    public function testParseResponseWithoutImagesField(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);
        $this->expectExceptionMessage('SiliconFlow 图片生成响应缺少 images 字段。');

        $config = new SiliconFlowConfig();
        $config->setName('test');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $request = new CreateImageGenerationRequest(
            $config,
            'Kwai-Kolors/Kolors',
            'test prompt'
        );

        $response = json_encode([
            'created' => time(),
            'model' => 'Kwai-Kolors/Kolors'
        ]);
        if ($response === false) {
            throw new \RuntimeException('Failed to encode test JSON response');
        }

        $request->parseResponse($response);
    }

    public function testParseResponseWithInvalidJson(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);

        $config = new SiliconFlowConfig();
        $config->setName('test');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $request = new CreateImageGenerationRequest(
            $config,
            'Kwai-Kolors/Kolors',
            'test prompt'
        );

        $request->parseResponse('invalid json');
    }

    public function testToArray(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('test');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $request = new CreateImageGenerationRequest(
            $config,
            'Kwai-Kolors/Kolors',
            'a beautiful landscape',
            negativePrompt: 'low quality',
            imageSize: '1024x1024',
            batchSize: 2,
            seed: 42,
            numInferenceSteps: 30,
            extraOptions: ['temperature' => 0.7, 'guidance_scale' => 7.5]
        );

        $result = $request->toArray();

        self::assertSame('Kwai-Kolors/Kolors', $result['model']);
        self::assertSame('a beautiful landscape', $result['prompt']);
        self::assertSame('low quality', $result['negative_prompt']);
        self::assertSame('1024x1024', $result['image_size']);
        self::assertSame(2, $result['batch_size']);
        self::assertSame(42, $result['seed']);
        self::assertSame(30, $result['num_inference_steps']);
        self::assertSame(0.7, $result['temperature']);
        self::assertSame(7.5, $result['guidance_scale']);
    }

    public function testToArrayWithMinimalParameters(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('test');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $request = new CreateImageGenerationRequest(
            $config,
            'Kwai-Kolors/Kolors',
            'simple prompt'
        );

        $result = $request->toArray();

        self::assertSame('Kwai-Kolors/Kolors', $result['model']);
        self::assertSame('simple prompt', $result['prompt']);
        self::assertArrayNotHasKey('negative_prompt', $result);
        self::assertArrayNotHasKey('image_size', $result);
        self::assertArrayNotHasKey('batch_size', $result);
        self::assertArrayNotHasKey('seed', $result);
        self::assertArrayNotHasKey('num_inference_steps', $result);
    }

    public function testValidatesBatchSize(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('test');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        // Test valid batch sizes
        $validBatchSizes = [1, 2, 3, 4];
        foreach ($validBatchSizes as $batchSize) {
            $request = new CreateImageGenerationRequest(
                $config,
                'Kwai-Kolors/Kolors',
                'test prompt',
                batchSize: $batchSize
            );

            // Should not throw exception
            $request->validatesBatchSize();
            self::assertSame($batchSize, $request->getBatchSize()); // Verify the batch size was set correctly
        }
    }

    public function testValidatesBatchSizeFailsForTooSmall(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);
        $this->expectExceptionMessage('批量生成数量 batch_size 需在 1 到 4 之间。');

        $config = new SiliconFlowConfig();
        $config->setName('test');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $request = new CreateImageGenerationRequest(
            $config,
            'Kwai-Kolors/Kolors',
            'test prompt',
            batchSize: 0
        );

        $request->validatesBatchSize();
    }

    public function testValidatesBatchSizeFailsForTooLarge(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);
        $this->expectExceptionMessage('批量生成数量 batch_size 需在 1 到 4 之间。');

        $config = new SiliconFlowConfig();
        $config->setName('test');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $request = new CreateImageGenerationRequest(
            $config,
            'Kwai-Kolors/Kolors',
            'test prompt',
            batchSize: 5
        );

        $request->validatesBatchSize();
    }

    public function testValidatesBatchSizeWithNullBatchSize(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('test');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $request = new CreateImageGenerationRequest(
            $config,
            'Kwai-Kolors/Kolors',
            'test prompt'
        );

        // Should not throw exception for null batch size
        $request->validatesBatchSize();
        self::assertNull($request->getBatchSize()); // Batch size should remain null when not set
    }
}
