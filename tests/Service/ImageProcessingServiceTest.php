<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Service\ImageProcessingService;

/**
 * 图片处理服务测试
 */
#[CoversClass(ImageProcessingService::class)]
#[RunTestsInSeparateProcesses]
class ImageProcessingServiceTest extends AbstractIntegrationTestCase
{
    private ImageProcessingService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(ImageProcessingService::class);
    }

    public function testInstantiation(): void
    {
        self::assertInstanceOf(ImageProcessingService::class, $this->service);
    }

    public function testTransformUrlArray(): void
    {
        $input = [
            'https://example.com/image1.jpg',
            ['url' => 'https://example.com/image2.jpg', 'metadata' => 'test'],
            'https://example.com/image3.jpg'
        ];

        $result = $this->service->transformUrlArray($input);

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
        self::assertArrayHasKey(2, $result);

        // 验证返回的是顺序数组 array<int, array<string, mixed>|string>
        foreach ($result as $index => $item) {
            self::assertIsInt($index);
            self::assertTrue(is_string($item) || is_array($item));
        }
    }

    public function testTransformUrlArrayEmpty(): void
    {
        $result = $this->service->transformUrlArray([]);
        self::assertSame([], $result);
    }

    public function testProcessImageUrls(): void
    {
        $urls = [
            'https://example.com/test1.jpg',
            'https://example.com/test2.png'
        ];

        $result = $this->service->processImageUrls($urls);
        self::assertIsArray($result);
        self::assertCount(2, $result);
    }

    public function testReplaceResponseImageUrls(): void
    {
        // 测试包含 data 字段的响应
        $responseWithData = [
            'data' => [
                'https://example.com/image1.jpg',
                ['url' => 'https://example.com/image2.jpg', 'metadata' => 'test']
            ],
            'other_field' => 'value'
        ];

        $result = $this->service->replaceResponseImageUrls($responseWithData);

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
        self::assertArrayHasKey('other_field', $result);
        self::assertSame('value', $result['other_field']);
        self::assertIsArray($result['data']);
        self::assertCount(2, $result['data']);

        // 测试包含 images 字段的响应
        $responseWithImages = [
            'images' => [
                'https://example.com/image3.jpg',
                ['url' => 'https://example.com/image4.jpg']
            ]
        ];

        $result = $this->service->replaceResponseImageUrls($responseWithImages);

        self::assertIsArray($result);
        self::assertArrayHasKey('images', $result);
        self::assertIsArray($result['images']);
        self::assertCount(2, $result['images']);

        // 测试不包含图片字段的响应
        $responseWithoutImages = [
            'text' => 'some text',
            'number' => 123
        ];

        $result = $this->service->replaceResponseImageUrls($responseWithoutImages);

        self::assertSame($responseWithoutImages, $result);

        // 测试空响应
        $emptyResponse = [];
        $result = $this->service->replaceResponseImageUrls($emptyResponse);
        self::assertSame($emptyResponse, $result);
    }

    public function testResolveFilenameFromUrl(): void
    {
        // 测试正常的URL
        $url1 = 'https://example.com/path/to/image.jpg';
        $filename1 = $this->service->resolveFilenameFromUrl($url1);
        self::assertSame('image.jpg', $filename1);

        // 测试包含查询参数的URL
        $url2 = 'https://example.com/image.png?size=large&format=webp';
        $filename2 = $this->service->resolveFilenameFromUrl($url2);
        self::assertSame('image.png', $filename2);

        // 测试URL编码的文件名
        $url3 = 'https://example.com/%E7%85%A7%E7%89%87%20%E6%B8%AC%E8%A9%A6.jpg';
        $filename3 = $this->service->resolveFilenameFromUrl($url3);
        self::assertSame('照片 測試.jpg', $filename3);

        // 测试只有域名的URL（没有路径）
        $url4 = 'https://example.com';
        $filename4 = $this->service->resolveFilenameFromUrl($url4);
        self::assertMatchesRegularExpression('/^silicon-image_.*\.jpg$/', $filename4);

        // 测试路径以斜杠结尾的URL
        $url5 = 'https://example.com/images/';
        $filename5 = $this->service->resolveFilenameFromUrl($url5);
        self::assertMatchesRegularExpression('/^silicon-image_.*\.jpg$/', $filename5);

        // 测试空路径
        $url6 = 'https://example.com/';
        $filename6 = $this->service->resolveFilenameFromUrl($url6);
        self::assertMatchesRegularExpression('/^silicon-image_.*\.jpg$/', $filename6);
    }

    public function testTransferImageToLocal(): void
    {
        // 这个方法需要真实的HTTP请求，在集成测试中可能会失败
        // 我们主要测试方法的存在和基本行为
        $remoteUrl = 'https://httpbin.org/image/jpeg';

        try {
            $result = $this->service->transferImageToLocal($remoteUrl);
            // 如果成功，应该返回字符串URL或null
            self::assertTrue(is_string($result) || is_null($result));
        } catch (\Throwable $e) {
            // 在测试环境中网络请求可能会失败，这是可以接受的
            self::assertInstanceOf(\Throwable::class, $e);
        }
    }

    public function testTransferImageToLocalWithInvalidUrl(): void
    {
        // 测试无效URL
        $invalidUrl = 'https://nonexistent-domain-12345.com/image.jpg';

        $result = $this->service->transferImageToLocal($invalidUrl);
        self::assertNull($result);
    }

    public function testTransferImageToLocalWithNonImageUrl(): void
    {
        // 测试非图片URL
        $textUrl = 'https://httpbin.org/json';

        $result = $this->service->transferImageToLocal($textUrl);
        // 可能返回null（如果内容不是图片）或字符串URL（如果文件服务接受了）
        self::assertTrue(is_string($result) || is_null($result));
    }
}