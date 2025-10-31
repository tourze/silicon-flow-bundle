<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowImageGenerationRepository;

#[CoversClass(SiliconFlowImageGenerationRepository::class)]
#[RunTestsInSeparateProcesses]
class SiliconFlowImageGenerationRepositoryTest extends AbstractIntegrationTestCase
{
    private SiliconFlowImageGenerationRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = $this->getService(SiliconFlowImageGenerationRepository::class);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(SiliconFlowImageGenerationRepository::class, $this->repository);
    }

    public function testCount(): void
    {
        $count = $this->repository->count([]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }
}
