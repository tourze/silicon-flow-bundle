<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\SiliconFlowBundle\SiliconFlowBundle;

/**
 * SiliconFlow Bundle 测试
 */
#[CoversClass(SiliconFlowBundle::class)]
#[RunTestsInSeparateProcesses]
final class SiliconFlowBundleTest extends AbstractBundleTestCase
{
}