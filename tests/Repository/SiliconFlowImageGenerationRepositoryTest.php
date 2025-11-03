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
        $this->repository = self::getService(SiliconFlowImageGenerationRepository::class);
    }

    public function testInstantiation(): void
    {
        self::assertInstanceOf(SiliconFlowImageGenerationRepository::class, $this->repository);
    }

    public function testCount(): void
    {
        $count = $this->repository->count([]);
        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
    }
}
