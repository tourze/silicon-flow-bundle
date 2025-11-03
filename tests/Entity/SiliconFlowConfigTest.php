<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;

/**
 * SiliconFlow 配置实体测试
 */
#[CoversClass(SiliconFlowConfig::class)]
#[RunTestsInSeparateProcesses]
class SiliconFlowConfigTest extends AbstractIntegrationTestCase
{
    private SiliconFlowConfig $entity;

    protected function onSetUp(): void
    {
        $this->entity = new SiliconFlowConfig();
    }

    /**
     * 测试实体实例化
     */
    public function testInstantiation(): void
    {
        self::assertInstanceOf(SiliconFlowConfig::class, $this->entity);
        self::assertTrue($this->entity->isActive()); // 默认启用
        self::assertSame(0, $this->entity->getPriority()); // 默认优先级为0
        self::assertSame('https://api.siliconflow.cn', $this->entity->getBaseUrl()); // 默认URL
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $this->entity->setName('测试配置');
        self::assertSame('测试配置', (string) $this->entity);
    }

    /**
     * 测试Name的getter和setter
     */
    public function testName(): void
    {
        $name = '生产环境配置';
        $this->entity->setName($name);
        self::assertSame($name, $this->entity->getName());
    }

    /**
     * 测试BaseUrl的getter和setter
     */
    public function testBaseUrl(): void
    {
        $baseUrl = 'https://custom.api.com';
        $this->entity->setBaseUrl($baseUrl);
        self::assertSame($baseUrl, $this->entity->getBaseUrl());

        // 测试自动去除末尾斜杠
        $this->entity->setBaseUrl('https://api.test.com/');
        self::assertSame('https://api.test.com', $this->entity->getBaseUrl());
    }

    /**
     * 测试ApiToken的getter和setter
     */
    public function testApiToken(): void
    {
        $token = 'sk-abc123def456';
        $this->entity->setApiToken($token);
        self::assertSame($token, $this->entity->getApiToken());
    }

    /**
     * 测试IsActive的getter和setter
     */
    public function testIsActive(): void
    {
        self::assertTrue($this->entity->isActive()); // 默认值

        $this->entity->setIsActive(false);
        self::assertFalse($this->entity->isActive());

        $this->entity->setIsActive(true);
        self::assertTrue($this->entity->isActive());
    }

    /**
     * 测试Priority的getter和setter
     */
    public function testPriority(): void
    {
        $priority = 100;
        $this->entity->setPriority($priority);
        self::assertSame($priority, $this->entity->getPriority());
    }

    /**
     * 测试Description的getter和setter
     */
    public function testDescription(): void
    {
        $description = '这是一个测试配置';
        $this->entity->setDescription($description);
        self::assertSame($description, $this->entity->getDescription());

        $this->entity->setDescription(null);
        self::assertNull($this->entity->getDescription());
    }

    /**
     * 测试activate方法
     */
    public function testActivate(): void
    {
        $this->entity->setIsActive(false);
        self::assertFalse($this->entity->isActive());

        $this->entity->activate();
        self::assertTrue($this->entity->isActive());
    }

    /**
     * 测试deactivate方法
     */
    public function testDeactivate(): void
    {
        $this->entity->setIsActive(true);
        self::assertTrue($this->entity->isActive());

        $this->entity->deactivate();
        self::assertFalse($this->entity->isActive());
    }

    /**
     * 测试完整的实体数据设置
     */
    public function testCompleteEntity(): void
    {
        $this->entity->setName('测试配置');
        $this->entity->setBaseUrl('https://test.api.com');
        $this->entity->setApiToken('test-token-123');
        $this->entity->setIsActive(false);
        $this->entity->setPriority(50);
        $this->entity->setDescription('测试用配置');

        self::assertSame('测试配置', $this->entity->getName());
        self::assertSame('https://test.api.com', $this->entity->getBaseUrl());
        self::assertSame('test-token-123', $this->entity->getApiToken());
        self::assertFalse($this->entity->isActive());
        self::assertSame(50, $this->entity->getPriority());
        self::assertSame('测试用配置', $this->entity->getDescription());
    }
}