<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Request;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\SiliconFlowBundle\Request\SubmitVideoRequest;

/**
 * 视频生成请求测试
 */
#[CoversClass(SubmitVideoRequest::class)]
class SubmitVideoRequestTest extends RequestTestCase
{
    public function testConstruct(): void
    {
        $request = new SubmitVideoRequest();
        $this->assertInstanceOf(SubmitVideoRequest::class, $request);
    }

    public function testGetMethod(): void
    {
        $request = new SubmitVideoRequest();
        $this->assertSame('POST', $request->getMethod());
    }

    public function testGetPath(): void
    {
        $request = new SubmitVideoRequest();
        $this->assertSame('/v1/video/submit', $request->getPath());
    }

    public function testGetHeaders(): void
    {
        $request = new SubmitVideoRequest();
        $headers = $request->getHeaders();

        $this->assertIsArray($headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBody(): void
    {
        $request = new SubmitVideoRequest();
        $request->setModel('cogvideox-flash');
        $request->setPrompt('A flying bird in the sky');

        $body = $request->getBody();
        $this->assertIsString($body);

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('model', $decoded);
        $this->assertArrayHasKey('prompt', $decoded);
        $this->assertSame('cogvideox-flash', $decoded['model']);
        $this->assertSame('A flying bird in the sky', $decoded['prompt']);
    }

    public function testGetBodyWithOptionalParams(): void
    {
        $request = new SubmitVideoRequest();
        $request->setModel('cogvideox-5b');
        $request->setPrompt('Ocean waves');
        $request->setNegativePrompt('blurry, low quality');
        $request->setImageSize('768x768');
        $request->setNumInferenceSteps(20);

        $body = $request->getBody();
        $decoded = json_decode($body, true);

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

        $request = new SubmitVideoRequest();
        $request->setModel('cogvideox-flash');
        $request->setPrompt('Animate this image');
        $request->setImage($imageBase64);

        $body = $request->getBody();
        $decoded = json_decode($body, true);

        $this->assertArrayHasKey('image', $decoded);
        $this->assertSame($imageBase64, $decoded['image']);
    }
}