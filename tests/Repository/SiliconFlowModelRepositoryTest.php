<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowModelRepository;

#[CoversClass(SiliconFlowModelRepository::class)]
#[RunTestsInSeparateProcesses]
class SiliconFlowModelRepositoryTest extends AbstractIntegrationTestCase
{
    private SiliconFlowModelRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = $this->getService(SiliconFlowModelRepository::class);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(SiliconFlowModelRepository::class, $this->repository);
    }

    public function testCount(): void
    {
        $count = $this->repository->count([]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testFindActiveModels(): void
    {
        $models = $this->repository->findActiveModels();
        $this->assertIsArray($models);
        foreach ($models as $model) {
            $this->assertInstanceOf(\Tourze\SiliconFlowBundle\Entity\SiliconFlowModel::class, $model);
        }
    }
}
