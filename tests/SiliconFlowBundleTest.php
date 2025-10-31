<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\SiliconFlowBundle;
use Tourze\SymfonyDependencyServiceLoader\SymfonyDependencyServiceLoaderBundle;

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
        $this->assertContains(DoctrineBundle::class, $dependencies);
        $this->assertContains(SymfonyDependencyServiceLoaderBundle::class, $dependencies);
    }

    public function testBundleIsFinal(): void
    {
        $reflection = new \ReflectionClass(SiliconFlowBundle::class);
        $this->assertTrue($reflection->isFinal(), 'SiliconFlowBundle should be final');
    }

    public function testExtendsBundle(): void
    {
        $reflection = new \ReflectionClass(SiliconFlowBundle::class);
        $this->assertSame('Symfony\Component\HttpKernel\Bundle\Bundle', $reflection->getParentClass()->getName());
    }

    public function testGetPath(): void
    {
        $path = $this->bundle->getPath();
        $this->assertIsString($path);
        $this->assertStringContains('silicon-flow-bundle', $path);
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