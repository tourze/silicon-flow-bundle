<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Exception\ApiException;
use Tourze\SiliconFlowBundle\Request\SubmitVideoRequest;

/**
 * 视频生成请求测试
 */
#[CoversClass(SubmitVideoRequest::class)]
class SubmitVideoRequestTest extends RequestTestCase
{
    private SiliconFlowConfig $config;

    protected function setUp(): void
    {
        $this->config = new SiliconFlowConfig();
        $this->config->setName('test');
        $this->config->setBaseUrl('https://api.siliconflow.cn');
        $this->config->setApiToken('test-token');
    }

    public function testConstruct(): void
    {
        $payload = [
            'model' => 'cogvideox-flash',
            'prompt' => 'A flying bird in the sky',
            'image_size' => '1280x720',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);
        $this->assertInstanceOf(SubmitVideoRequest::class, $request);
    }

    public function testGetMethod(): void
    {
        $payload = [
            'model' => 'cogvideox-flash',
            'prompt' => 'A flying bird in the sky',
            'image_size' => '1280x720',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);
        $this->assertSame('POST', $request->getRequestMethod());
    }

    public function testGetPath(): void
    {
        $payload = [
            'model' => 'cogvideox-flash',
            'prompt' => 'A flying bird in the sky',
            'image_size' => '1280x720',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);
        $this->assertSame('/v1/video/submit', $request->getRequestPath());
    }

    public function testGetHeaders(): void
    {
        $payload = [
            'model' => 'cogvideox-flash',
            'prompt' => 'A flying bird in the sky',
            'image_size' => '1280x720',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);
        $options = $request->getRequestOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('headers', $options);

        $headers = $options['headers'];
        $this->assertIsArray($headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertSame('Bearer test-token', $headers['Authorization']);
    }

    public function testGetBody(): void
    {
        $payload = [
            'model' => 'cogvideox-flash',
            'prompt' => 'A flying bird in the sky',
            'image_size' => '1280x720',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);
        $options = $request->getRequestOptions();

        $this->assertArrayHasKey('json', $options);
        $decoded = $options['json'];
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('model', $decoded);
        $this->assertArrayHasKey('prompt', $decoded);
        $this->assertSame('cogvideox-flash', $decoded['model']);
        $this->assertSame('A flying bird in the sky', $decoded['prompt']);
    }

    public function testGetBodyWithOptionalParams(): void
    {
        $payload = [
            'model' => 'cogvideox-5b',
            'prompt' => 'Ocean waves',
            'negative_prompt' => 'blurry, low quality',
            'image_size' => '768x768',
            'num_inference_steps' => 20,
        ];
        $request = new SubmitVideoRequest($this->config, $payload);
        $options = $request->getRequestOptions();

        $this->assertArrayHasKey('json', $options);
        $decoded = $options['json'];
        $this->assertIsArray($decoded);

        $this->assertArrayHasKey('negative_prompt', $decoded);
        $this->assertArrayHasKey('image_size', $decoded);
        $this->assertArrayHasKey('num_inference_steps', $decoded);
        $this->assertSame('blurry, low quality', $decoded['negative_prompt']);
        $this->assertSame('768x768', $decoded['image_size']);
        $this->assertSame(20, $decoded['num_inference_steps']);
    }

    public function testGetBodyWithImage(): void
    {
        $imageBase64 = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAA...';

        $payload = [
            'model' => 'cogvideox-flash',
            'prompt' => 'Animate this image',
            'image' => $imageBase64,
            'image_size' => '1280x720',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);
        $options = $request->getRequestOptions();

        $this->assertArrayHasKey('json', $options);
        $decoded = $options['json'];
        $this->assertIsArray($decoded);

        $this->assertArrayHasKey('image', $decoded);
        $this->assertSame($imageBase64, $decoded['image']);
    }

    public function testValidate(): void
    {
        $payload = [
            'model' => 'cogvideox-flash',
            'prompt' => 'A flying bird in the sky',
            'image_size' => '1280x720',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);

        // Should not throw when all required fields are present
        $request->validate();
        $this->assertTrue(true);
    }

    public function testValidateWithoutModel(): void
    {
        $payload = [
            'prompt' => 'A flying bird in the sky',
            'image_size' => '1280x720',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Video Submit 请求必须指定模型。');
        $request->validate();
    }

    public function testValidateWithoutPrompt(): void
    {
        $payload = [
            'model' => 'cogvideox-flash',
            'image_size' => '1280x720',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Video Submit 请求必须提供 prompt。');
        $request->validate();
    }

    public function testValidateWithoutImageSize(): void
    {
        $payload = [
            'model' => 'cogvideox-flash',
            'prompt' => 'A flying bird in the sky',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Video Submit 请求必须指定 image_size。');
        $request->validate();
    }

    public function testValidateWithInvalidImageSize(): void
    {
        $payload = [
            'model' => 'cogvideox-flash',
            'prompt' => 'A flying bird in the sky',
            'image_size' => 'invalid',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('image_size 必须符合 widthxheight 格式，例如 1280x720。');
        $request->validate();
    }

    public function testParseResponse(): void
    {
        $payload = [
            'model' => 'cogvideox-flash',
            'prompt' => 'A flying bird in the sky',
            'image_size' => '1280x720',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);

        $responseContent = (string) json_encode([
            'requestId' => 'req-123456',
            'status' => 'submitted',
        ]);

        $result = $request->parseResponse($responseContent);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('requestId', $result);
        $this->assertSame('req-123456', $result['requestId']);
    }

    public function testParseResponseWithMissingRequestId(): void
    {
        $payload = [
            'model' => 'cogvideox-flash',
            'prompt' => 'A flying bird in the sky',
            'image_size' => '1280x720',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);

        $responseContent = (string) json_encode([
            'status' => 'submitted',
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('视频生成提交接口响应缺少 requestId 字段。');
        $request->parseResponse($responseContent);
    }

    public function testParseResponseWithInvalidJson(): void
    {
        $payload = [
            'model' => 'cogvideox-flash',
            'prompt' => 'A flying bird in the sky',
            'image_size' => '1280x720',
        ];
        $request = new SubmitVideoRequest($this->config, $payload);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('SiliconFlow 返回了无法解析的响应：');
        $request->parseResponse('invalid json');
    }
}