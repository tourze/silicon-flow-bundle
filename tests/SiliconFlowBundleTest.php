<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\SiliconFlowBundle;

/**
 * SiliconFlow Bundle 测试
 */
#[CoversClass(SiliconFlowBundle::class)]
#[RunTestsInSeparateProcesses]
class SiliconFlowBundleTest extends AbstractIntegrationTestCase
{
    private SiliconFlowBundle $bundle;

    protected function onSetUp(): void
    {
        $this->bundle = new SiliconFlowBundle();
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(SiliconFlowBundle::class, $this->bundle);
    }

    public function testImplementsBundleDependencyInterface(): void
    {
        $this->assertInstanceOf(BundleDependencyInterface::class, $this->bundle);
    }

    public function testGetBundleDependencies(): void
    {
        $dependencies = SiliconFlowBundle::getBundleDependencies();

        $this->assertIsArray($dependencies);
        $this->assertArrayHasKey(DoctrineBundle::class, $dependencies);
        $this->assertSame(['all' => true], $dependencies[DoctrineBundle::class]);
    }

    public function testBundleIsFinal(): void
    {
        $reflection = new \ReflectionClass(SiliconFlowBundle::class);
        $this->assertTrue($reflection->isFinal(), 'SiliconFlowBundle should be final');
    }

    public function testExtendsBundle(): void
    {
        $reflection = new \ReflectionClass(SiliconFlowBundle::class);
        $parentClass = $reflection->getParentClass();
        $this->assertNotFalse($parentClass, 'SiliconFlowBundle should extend a parent class');
        $this->assertSame('Symfony\Component\HttpKernel\Bundle\Bundle', $parentClass->getName());
    }

    public function testGetPath(): void
    {
        $path = $this->bundle->getPath();
        $this->assertIsString($path);
        $this->assertStringContainsString('silicon-flow-bundle', $path);
    }

    public function testGetName(): void
    {
        $name = $this->bundle->getName();
        $this->assertSame('SiliconFlowBundle', $name);
    }

    public function testGetNamespace(): void
    {
        $namespace = $this->bundle->getNamespace();
        $this->assertSame('Tourze\SiliconFlowBundle', $namespace);
    }
}