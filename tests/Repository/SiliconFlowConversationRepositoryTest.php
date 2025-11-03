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
        $this->repository = self::getService(SiliconFlowConversationRepository::class);
    }

    public function testInstantiation(): void
    {
        self::assertInstanceOf(SiliconFlowConversationRepository::class, $this->repository);
    }

    public function testCount(): void
    {
        $count = $this->repository->count([]);
        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
    }
}
