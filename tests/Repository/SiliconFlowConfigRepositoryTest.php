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
        $this->repository = self::getService(SiliconFlowConfigRepository::class);
    }

    public function testInstantiation(): void
    {
        self::assertInstanceOf(SiliconFlowConfigRepository::class, $this->repository);
    }

    public function testCount(): void
    {
        $count = $this->repository->count([]);
        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testFindActiveConfig(): void
    {
        $config = $this->repository->findActiveConfig();
        if ($config !== null) {
            self::assertInstanceOf(\Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig::class, $config);
        }
        // else 分支中 $config 已被类型系统收窄为 null，无需额外断言
    }

    public function testFindActiveConfigs(): void
    {
        $configs = $this->repository->findActiveConfigs(10);
        self::assertIsArray($configs);
        self::assertLessThanOrEqual(10, count($configs));
    }

    public function testFindActiveConfigsWithDefaultLimit(): void
    {
        $configs = $this->repository->findActiveConfigs();
        self::assertIsArray($configs);
        self::assertLessThanOrEqual(50, count($configs));
    }
}
