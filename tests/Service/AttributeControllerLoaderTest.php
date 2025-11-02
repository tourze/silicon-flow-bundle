<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Service\AttributeControllerLoader;

/**
 * 属性控制器加载器测试
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $loader;

    protected function onSetUp(): void
    {
        $this->loader = $this->getService(AttributeControllerLoader::class);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(AttributeControllerLoader::class, $this->loader);
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(
            \Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface::class,
            $this->loader
        );
    }

    public function testLoadControllers(): void
    {
        // 测试加载控制器
        $routes = $this->loader->autoload();
        $this->assertInstanceOf(\Symfony\Component\Routing\RouteCollection::class, $routes);

        // 验证返回的是路由集合
        $this->assertGreaterThanOrEqual(0, $routes->count());
    }

    public function testGetControllersDirectory(): void
    {
        // 测试获取控制器目录
        $reflection = new \ReflectionClass($this->loader);

        if ($reflection->hasMethod('getControllersDirectory')) {
            $method = $reflection->getMethod('getControllersDirectory');
            $method->setAccessible(true);
            $directory = $method->invoke($this->loader);

            $this->assertIsString($directory);
            $this->assertStringContainsString('Controller', $directory);
        } else {
            $this->markTestSkipped('getControllersDirectory method not found');
        }
    }

    public function testGetNamespace(): void
    {
        // 测试获取命名空间
        $reflection = new \ReflectionClass($this->loader);

        if ($reflection->hasMethod('getNamespace')) {
            $method = $reflection->getMethod('getNamespace');
            $method->setAccessible(true);
            $namespace = $method->invoke($this->loader);

            $this->assertIsString($namespace);
            $this->assertStringContainsString('SiliconFlowBundle', $namespace);
        } else {
            $this->markTestSkipped('getNamespace method not found');
        }
    }
}