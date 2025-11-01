<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Exception\ApiException;
use Tourze\SiliconFlowBundle\Request\SubmitVideoRequest;

#[CoversClass(SubmitVideoRequest::class)]
final class VideoRequestsTest extends RequestTestCase
{
    public function testSubmitVideoRequestBuildsPayload(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('video');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('video-token');

        $payload = [
            'model' => 'Wan-AI/Wan2.1-T2V-14B',
            'prompt' => 'a robot dancing in space',
            'image_size' => '1280x720',
            'negative_prompt' => 'blurry',
        ];

        $request = new SubmitVideoRequest($config, $payload);
        $options = $request->getRequestOptions();
        self::assertArrayHasKey('json', $options);
        self::assertArrayHasKey('headers', $options);

        self::assertSame($payload, $options['json']);

        $headers = $options['headers'];
        self::assertIsArray($headers);
        self::assertSame('Bearer video-token', $headers['Authorization']);
    }

    public function testSubmitVideoRequestValidation(): void
    {
        $this->expectException(ApiException::class);

        $config = new SiliconFlowConfig();
        $config->setName('video');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('video-token');

        $request = new SubmitVideoRequest($config, [
            'model' => 'Wan-AI/Wan2.1-T2V-14B',
            'prompt' => 'hello',
            'image_size' => 'invalid',
        ]);

        $request->validate();
    }

    
    public function testSubmitVideoRequestParseResponse(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('video');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('video-token');

        $payload = [
            'model' => 'Wan-AI/Wan2.1-T2V-14B',
            'prompt' => 'a robot dancing in space',
            'image_size' => '1280x720',
        ];

        $request = new SubmitVideoRequest($config, $payload);

        $response = json_encode([
            'requestId' => 'req-abc123',
            'status' => 'processing',
            'created' => time(),
            'model' => 'Wan-AI/Wan2.1-T2V-14B'
        ]);
        self::assertIsString($response);

        $result = $request->parseResponse($response);

        self::assertArrayHasKey('requestId', $result);
        self::assertSame('req-abc123', $result['requestId']);
        self::assertArrayHasKey('status', $result);
        self::assertSame('processing', $result['status']);
        self::assertArrayHasKey('model', $result);
        self::assertSame('Wan-AI/Wan2.1-T2V-14B', $result['model']);
    }

    public function testSubmitVideoRequestParseResponseWithoutRequestId(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);
        $this->expectExceptionMessage('视频生成提交接口响应缺少 requestId 字段。');

        $config = new SiliconFlowConfig();
        $config->setName('video');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('video-token');

        $payload = [
            'model' => 'Wan-AI/Wan2.1-T2V-14B',
            'prompt' => 'test prompt',
            'image_size' => '1280x720',
        ];

        $request = new SubmitVideoRequest($config, $payload);

        $response = json_encode([
            'status' => 'processing',
            'created' => time(),
            'model' => 'Wan-AI/Wan2.1-T2V-14B'
        ]);

        self::assertIsString($response, 'json_encode should return a string');
        $request->parseResponse($response);
    }

    public function testSubmitVideoRequestParseResponseWithInvalidJson(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);

        $config = new SiliconFlowConfig();
        $config->setName('video');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('video-token');

        $payload = [
            'model' => 'Wan-AI/Wan2.1-T2V-14B',
            'prompt' => 'test prompt',
            'image_size' => '1280x720',
        ];

        $request = new SubmitVideoRequest($config, $payload);

        $request->parseResponse('invalid json');
    }

    public function testSubmitVideoRequestValidationWithMissingModel(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);
        $this->expectExceptionMessage('Video Submit 请求必须指定模型。');

        $config = new SiliconFlowConfig();
        $config->setName('video');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('video-token');

        $request = new SubmitVideoRequest($config, [
            'prompt' => 'a robot dancing in space',
            'image_size' => '1280x720',
        ]);

        $request->validate();
    }

    public function testSubmitVideoRequestValidationWithEmptyModel(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);
        $this->expectExceptionMessage('Video Submit 请求必须指定模型。');

        $config = new SiliconFlowConfig();
        $config->setName('video');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('video-token');

        $request = new SubmitVideoRequest($config, [
            'model' => '',
            'prompt' => 'a robot dancing in space',
            'image_size' => '1280x720',
        ]);

        $request->validate();
    }

    public function testSubmitVideoRequestValidationWithMissingPrompt(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);
        $this->expectExceptionMessage('Video Submit 请求必须提供 prompt。');

        $config = new SiliconFlowConfig();
        $config->setName('video');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('video-token');

        $request = new SubmitVideoRequest($config, [
            'model' => 'Wan-AI/Wan2.1-T2V-14B',
            'image_size' => '1280x720',
        ]);

        $request->validate();
    }

    public function testSubmitVideoRequestValidationWithEmptyPrompt(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);
        $this->expectExceptionMessage('Video Submit 请求必须提供 prompt。');

        $config = new SiliconFlowConfig();
        $config->setName('video');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('video-token');

        $request = new SubmitVideoRequest($config, [
            'model' => 'Wan-AI/Wan2.1-T2V-14B',
            'prompt' => '',
            'image_size' => '1280x720',
        ]);

        $request->validate();
    }

    public function testSubmitVideoRequestValidationWithMissingImageSize(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);
        $this->expectExceptionMessage('Video Submit 请求必须指定 image_size。');

        $config = new SiliconFlowConfig();
        $config->setName('video');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('video-token');

        $request = new SubmitVideoRequest($config, [
            'model' => 'Wan-AI/Wan2.1-T2V-14B',
            'prompt' => 'a robot dancing in space',
        ]);

        $request->validate();
    }

    public function testSubmitVideoRequestValidationWithInvalidImageSize(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);
        $this->expectExceptionMessage('image_size 必须符合 widthxheight 格式，例如 1280x720。');

        $config = new SiliconFlowConfig();
        $config->setName('video');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('video-token');

        $request = new SubmitVideoRequest($config, [
            'model' => 'Wan-AI/Wan2.1-T2V-14B',
            'prompt' => 'a robot dancing in space',
            'image_size' => 'invalid-format',
        ]);

        $request->validate();
    }

    public function testSubmitVideoRequestValidationSuccess(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('video');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('video-token');

        $payload = [
            'model' => 'Wan-AI/Wan2.1-T2V-14B',
            'prompt' => 'a robot dancing in space',
            'image_size' => '1280x720',
            'negative_prompt' => 'blurry',
            'seed' => 42,
        ];

        $request = new SubmitVideoRequest($config, $payload);

        // Should not throw exception
        $request->validate();
        // If we reach this point, validation passed
        $this->expectNotToPerformAssertions();
    }

    public function testParseResponse(): void
    {
        $config = new SiliconFlowConfig();
        $config->setApiToken('test-token');
        $payload = [
            'model' => 'Wan-AI/Wan2.1-T2V-14B',
            'prompt' => 'a robot dancing in space',
            'image_size' => '1280x720',
        ];

        $request = new SubmitVideoRequest($config, $payload);

        $response = json_encode([
            'requestId' => 'req-abc123',
            'status' => 'processing',
            'created' => time(),
            'model' => 'Wan-AI/Wan2.1-T2V-14B'
        ]);
        self::assertIsString($response);

        $result = $request->parseResponse($response);

        self::assertArrayHasKey('requestId', $result);
        self::assertSame('req-abc123', $result['requestId']);
    }

    public function testValidate(): void
    {
        $config = new SiliconFlowConfig();
        $config->setApiToken('test-token');
        $payload = [
            'model' => 'Wan-AI/Wan2.1-T2V-14B',
            'prompt' => 'a robot dancing in space',
            'image_size' => '1280x720',
        ];

        $request = new SubmitVideoRequest($config, $payload);

        // Should not throw exception
        $request->validate();
        $this->expectNotToPerformAssertions();
    }
}
