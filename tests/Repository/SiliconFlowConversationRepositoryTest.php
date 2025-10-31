<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowConversationRepository;

#[CoversClass(SiliconFlowConversationRepository::class)]
#[RunTestsInSeparateProcesses]
class SiliconFlowConversationRepositoryTest extends AbstractIntegrationTestCase
{
    private SiliconFlowConversationRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = $this->getService(SiliconFlowConversationRepository::class);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(SiliconFlowConversationRepository::class, $this->repository);
    }

    public function testCount(): void
    {
        $count = $this->repository->count([]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }
}
