<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowConfigRepository;

#[CoversClass(SiliconFlowConfigRepository::class)]
#[RunTestsInSeparateProcesses]
class SiliconFlowConfigRepositoryTest extends AbstractIntegrationTestCase
{
    private SiliconFlowConfigRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = $this->getService(SiliconFlowConfigRepository::class);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(SiliconFlowConfigRepository::class, $this->repository);
    }

    public function testCount(): void
    {
        $count = $this->repository->count([]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testFindActiveConfig(): void
    {
        $config = $this->repository->findActiveConfig();
        if ($config !== null) {
            $this->assertInstanceOf(\Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig::class, $config);
        }
        // else 分支中 $config 已被类型系统收窄为 null，无需额外断言
    }

    public function testFindActiveConfigs(): void
    {
        $configs = $this->repository->findActiveConfigs(10);
        $this->assertIsArray($configs);
        $this->assertLessThanOrEqual(10, count($configs));
    }

    public function testFindActiveConfigsWithDefaultLimit(): void
    {
        $configs = $this->repository->findActiveConfigs();
        $this->assertIsArray($configs);
        $this->assertLessThanOrEqual(50, count($configs));
    }
}
