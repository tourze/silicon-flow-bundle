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
        $this->service = $this->getService(ImageProcessingService::class);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(ImageProcessingService::class, $this->service);
    }

    public function testTransformUrlArray(): void
    {
        $input = [
            'https://example.com/image1.jpg',
            ['url' => 'https://example.com/image2.jpg', 'metadata' => 'test'],
            'https://example.com/image3.jpg'
        ];

        $result = $this->service->transformUrlArray($input);

        $this->assertIsArray($result);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertArrayHasKey(2, $result);

        // 验证返回的是顺序数组 array<int, array<string, mixed>|string>
        foreach ($result as $index => $item) {
            $this->assertIsInt($index);
            $this->assertTrue(is_string($item) || is_array($item));
        }
    }

    public function testTransformUrlArrayEmpty(): void
    {
        $result = $this->service->transformUrlArray([]);
        $this->assertSame([], $result);
    }

    public function testProcessImageUrls(): void
    {
        $urls = [
            'https://example.com/test1.jpg',
            'https://example.com/test2.png'
        ];

        $result = $this->service->processImageUrls($urls);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }
}