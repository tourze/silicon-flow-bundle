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
        $this->extractor = self::getService(ResponseExtractor::class);
    }

    public function testInstantiation(): void
    {
        self::assertInstanceOf(ResponseExtractor::class, $this->extractor);
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

        self::assertIsArray($result);
        self::assertArrayHasKey('choices', $result);
        self::assertArrayHasKey('usage', $result);
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

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
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

        self::assertIsArray($result);
        self::assertArrayHasKey('error', $result);
        $error = $result['error'];
        self::assertIsArray($error);
        self::assertSame('Invalid API key', $error['message']);
    }

    public function testExtractAnswerFromMessagesResponseWithValidTextBlock(): void
    {
        $response = [
            'content' => [
                ['type' => 'text', 'text' => 'Hello, world!']
            ]
        ];

        $result = $this->extractor->extractAnswerFromMessagesResponse($response);

        self::assertSame('Hello, world!', $result);
    }

    public function testExtractAnswerFromMessagesResponseWithMissingContent(): void
    {
        $response = [];

        $result = $this->extractor->extractAnswerFromMessagesResponse($response);

        self::assertSame('', $result);
    }

    public function testExtractAnswerFromMessagesResponseWithNonArrayContent(): void
    {
        $response = ['content' => 'not an array'];

        $result = $this->extractor->extractAnswerFromMessagesResponse($response);

        self::assertSame('', $result);
    }

    public function testExtractAnswerFromMessagesResponseWithNoTextBlock(): void
    {
        $response = [
            'content' => [
                ['type' => 'image', 'url' => 'https://example.com/image.jpg']
            ]
        ];

        $result = $this->extractor->extractAnswerFromMessagesResponse($response);

        self::assertSame('', $result);
    }

    public function testExtractImageUrlsWithValidImages(): void
    {
        $response = [
            'images' => [
                ['url' => 'https://example.com/image1.jpg'],
                ['url' => 'https://example.com/image2.jpg']
            ]
        ];

        $result = $this->extractor->extractImageUrls($response);

        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertSame('https://example.com/image1.jpg', $result[0]);
        self::assertSame('https://example.com/image2.jpg', $result[1]);
    }

    public function testExtractImageUrlsWithMissingImages(): void
    {
        $response = [];

        $result = $this->extractor->extractImageUrls($response);

        self::assertNull($result);
    }

    public function testExtractImageUrlsWithNonArrayImages(): void
    {
        $response = ['images' => 'not an array'];

        $result = $this->extractor->extractImageUrls($response);

        self::assertNull($result);
    }

    public function testExtractImageUrlsWithNoValidUrls(): void
    {
        $response = [
            'images' => [
                ['url' => ''],
                ['invalid' => 'data']
            ]
        ];

        $result = $this->extractor->extractImageUrls($response);

        self::assertNull($result);
    }

    public function testExtractVideoUrlsWithValidVideos(): void
    {
        $response = [
            'results' => [
                'videos' => [
                    ['url' => 'https://example.com/video1.mp4'],
                    ['url' => 'https://example.com/video2.mp4']
                ]
            ]
        ];

        $result = $this->extractor->extractVideoUrls($response);

        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertSame('https://example.com/video1.mp4', $result[0]);
        self::assertSame('https://example.com/video2.mp4', $result[1]);
    }

    public function testExtractVideoUrlsWithMissingResults(): void
    {
        $response = [];

        $result = $this->extractor->extractVideoUrls($response);

        self::assertNull($result);
    }

    public function testExtractVideoUrlsWithNonArrayResults(): void
    {
        $response = ['results' => 'not an array'];

        $result = $this->extractor->extractVideoUrls($response);

        self::assertNull($result);
    }

    public function testExtractVideoUrlsWithMissingVideos(): void
    {
        $response = ['results' => []];

        $result = $this->extractor->extractVideoUrls($response);

        self::assertNull($result);
    }

    public function testStringifyUrlsWithValidUrls(): void
    {
        $urls = ['https://example.com/1.jpg', 'https://example.com/2.jpg'];

        $result = $this->extractor->stringifyUrls($urls);

        self::assertSame('https://example.com/1.jpg,https://example.com/2.jpg', $result);
    }

    public function testStringifyUrlsWithNull(): void
    {
        $result = $this->extractor->stringifyUrls(null);

        self::assertNull($result);
    }

    public function testStringifyUrlsWithEmptyArray(): void
    {
        $result = $this->extractor->stringifyUrls([]);

        self::assertNull($result);
    }

    public function testExtractStatusWithValidStatus(): void
    {
        $response = ['status' => 'completed'];

        $result = $this->extractor->extractStatus($response, 'default');

        self::assertSame('completed', $result);
    }

    public function testExtractStatusWithMissingStatus(): void
    {
        $response = [];

        $result = $this->extractor->extractStatus($response, 'default');

        self::assertSame('default', $result);
    }

    public function testExtractStatusWithNonStringStatus(): void
    {
        $response = ['status' => 123];

        $result = $this->extractor->extractStatus($response, 'default');

        self::assertSame('default', $result);
    }

    public function testExtractStatusReasonWithValidReason(): void
    {
        $response = ['reason' => 'Task completed successfully'];

        $result = $this->extractor->extractStatusReason($response);

        self::assertSame('Task completed successfully', $result);
    }

    public function testExtractStatusReasonWithMissingReason(): void
    {
        $response = [];

        $result = $this->extractor->extractStatusReason($response);

        self::assertNull($result);
    }

    public function testExtractStatusReasonWithNonStringReason(): void
    {
        $response = ['reason' => 456];

        $result = $this->extractor->extractStatusReason($response);

        self::assertNull($result);
    }

    public function testExtractInferenceTimeWithValidTime(): void
    {
        $response = [
            'results' => [
                'timings' => [
                    'inference' => 1.23
                ]
            ]
        ];

        $result = $this->extractor->extractInferenceTime($response);

        self::assertSame(1.23, $result);
    }

    public function testExtractInferenceTimeWithIntegerValue(): void
    {
        $response = [
            'results' => [
                'timings' => [
                    'inference' => 5
                ]
            ]
        ];

        $result = $this->extractor->extractInferenceTime($response);

        self::assertSame(5.0, $result);
    }

    public function testExtractInferenceTimeWithMissingResults(): void
    {
        $response = [];

        $result = $this->extractor->extractInferenceTime($response);

        self::assertNull($result);
    }

    public function testExtractInferenceTimeWithMissingTimings(): void
    {
        $response = ['results' => []];

        $result = $this->extractor->extractInferenceTime($response);

        self::assertNull($result);
    }

    public function testExtractInferenceTimeWithMissingInference(): void
    {
        $response = ['results' => ['timings' => []]];

        $result = $this->extractor->extractInferenceTime($response);

        self::assertNull($result);
    }

    public function testExtractResultSeedWithValidSeed(): void
    {
        $response = [
            'results' => [
                'seed' => 12345
            ]
        ];

        $result = $this->extractor->extractResultSeed($response);

        self::assertSame(12345, $result);
    }

    public function testExtractResultSeedWithStringNumericValue(): void
    {
        $response = [
            'results' => [
                'seed' => '67890'
            ]
        ];

        $result = $this->extractor->extractResultSeed($response);

        self::assertSame(67890, $result);
    }

    public function testExtractResultSeedWithMissingResults(): void
    {
        $response = [];

        $result = $this->extractor->extractResultSeed($response);

        self::assertNull($result);
    }

    public function testExtractResultSeedWithMissingSeed(): void
    {
        $response = ['results' => []];

        $result = $this->extractor->extractResultSeed($response);

        self::assertNull($result);
    }

    public function testExtractResultSeedWithNonNumericSeed(): void
    {
        $response = ['results' => ['seed' => 'not-numeric']];

        $result = $this->extractor->extractResultSeed($response);

        self::assertNull($result);
    }
}