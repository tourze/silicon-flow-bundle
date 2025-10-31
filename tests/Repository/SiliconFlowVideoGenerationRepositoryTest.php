<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowVideoGenerationRepository;

#[CoversClass(SiliconFlowVideoGenerationRepository::class)]
#[RunTestsInSeparateProcesses]
class SiliconFlowVideoGenerationRepositoryTest extends AbstractIntegrationTestCase
{
    private SiliconFlowVideoGenerationRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = $this->getService(SiliconFlowVideoGenerationRepository::class);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(SiliconFlowVideoGenerationRepository::class, $this->repository);
    }

    public function testCount(): void
    {
        $count = $this->repository->count([]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }
}
