<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Request\GetVideoStatusRequest;

/**
 * SiliconFlow 视频状态请求测试
 */
#[CoversClass(GetVideoStatusRequest::class)]
final class GetVideoStatusRequestTest extends RequestTestCase
{
    public function testGetVideoStatusRequest(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('video');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('video-token');

        $request = new GetVideoStatusRequest($config, 'req-123');
        $options = $request->getRequestOptions();
        self::assertArrayHasKey('json', $options);

        $json = $options['json'];
        self::assertIsArray($json);
        self::assertSame('req-123', $json['requestId']);
    }

    public function testValidateWithEmptyRequestId(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);
        $this->expectExceptionMessage('Video Status 请求必须提供 requestId。');

        $config = new SiliconFlowConfig();
        $config->setApiToken('test-token');
        $request = new GetVideoStatusRequest($config, '');
        $request->validate();
    }

    public function testValidateWithWhitespaceOnlyRequestId(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);
        $this->expectExceptionMessage('Video Status 请求必须提供 requestId。');

        $config = new SiliconFlowConfig();
        $config->setApiToken('test-token');
        $request = new GetVideoStatusRequest($config, '   ');
        $request->validate();
    }

    public function testValidateSuccess(): void
    {
        $config = new SiliconFlowConfig();
        $config->setApiToken('test-token');
        $request = new GetVideoStatusRequest($config, 'valid-id');

        // Should not throw exception
        $request->validate();
        $this->expectNotToPerformAssertions();
    }

    public function testParseResponse(): void
    {
        $config = new SiliconFlowConfig();
        $config->setApiToken('test-token');
        $request = new GetVideoStatusRequest($config, 'req-123');

        $response = json_encode([
            'requestId' => 'req-123',
            'status' => 'completed',
            'result' => ['video_url' => 'https://example.com/video.mp4']
        ]);
        self::assertIsString($response);

        $result = $request->parseResponse($response);

        self::assertArrayHasKey('requestId', $result);
        self::assertSame('req-123', $result['requestId']);
        self::assertArrayHasKey('status', $result);
        self::assertSame('completed', $result['status']);
    }
}