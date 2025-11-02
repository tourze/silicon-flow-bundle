<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Service\ResponseExtractor;

/**
 * 响应解析器测试
 */
#[CoversClass(ResponseExtractor::class)]
#[RunTestsInSeparateProcesses]
class ResponseExtractorTest extends AbstractIntegrationTestCase
{
    private ResponseExtractor $extractor;

    protected function onSetUp(): void
    {
        $this->extractor = $this->getService(ResponseExtractor::class);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(ResponseExtractor::class, $this->extractor);
    }

    public function testExtractCompletionResponse(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getContent')
            ->willReturn(json_encode([
                'choices' => [
                    ['message' => ['content' => 'Hello, world!']]
                ],
                'usage' => ['total_tokens' => 10]
            ]));

        $result = $this->extractor->extractCompletionResponse($mockResponse);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('choices', $result);
        $this->assertArrayHasKey('usage', $result);
    }

    public function testExtractImageGenerationResponse(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getContent')
            ->willReturn(json_encode([
                'data' => [
                    ['url' => 'https://example.com/generated-image.jpg']
                ]
            ]));

        $result = $this->extractor->extractImageGenerationResponse($mockResponse);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
    }

    public function testExtractErrorResponse(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getContent')
            ->willReturn(json_encode([
                'error' => [
                    'message' => 'Invalid API key',
                    'code' => 'invalid_api_key'
                ]
            ]));

        $result = $this->extractor->extractErrorResponse($mockResponse);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $error = $result['error'];
        $this->assertIsArray($error);
        $this->assertSame('Invalid API key', $error['message']);
    }
}