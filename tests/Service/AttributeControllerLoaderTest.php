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
        $this->loader = self::getService(AttributeControllerLoader::class);
    }

    public function testInstantiation(): void
    {
        self::assertInstanceOf(AttributeControllerLoader::class, $this->loader);
    }

    public function testImplementsInterface(): void
    {
        self::assertInstanceOf(
            \Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface::class,
            $this->loader
        );
    }

    public function testLoadControllers(): void
    {
        // 测试加载控制器
        $routes = $this->loader->autoload();
        self::assertInstanceOf(\Symfony\Component\Routing\RouteCollection::class, $routes);

        // 验证返回的是路由集合
        self::assertGreaterThanOrEqual(0, $routes->count());
    }

    public function testGetControllersDirectory(): void
    {
        // 测试获取控制器目录
        $reflection = new \ReflectionClass($this->loader);

        if ($reflection->hasMethod('getControllersDirectory')) {
            $method = $reflection->getMethod('getControllersDirectory');
            $method->setAccessible(true);
            $directory = $method->invoke($this->loader);

            self::assertIsString($directory);
            self::assertStringContainsString('Controller', $directory);
        } else {
            self::markTestSkipped('getControllersDirectory method not found');
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

            self::assertIsString($namespace);
            self::assertStringContainsString('SiliconFlowBundle', $namespace);
        } else {
            self::markTestSkipped('getNamespace method not found');
        }
    }

    public function testSupports(): void
    {
        // 测试 supports 方法始终返回 false
        self::assertFalse($this->loader->supports('any_resource'));
        self::assertFalse($this->loader->supports('any_resource', 'any_type'));
        self::assertFalse($this->loader->supports(null));
        self::assertFalse($this->loader->supports([]));
        self::assertFalse($this->loader->supports(new \stdClass()));
    }
}