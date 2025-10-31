<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Repository\ChatCompletionLogRepository;

#[CoversClass(ChatCompletionLogRepository::class)]
#[RunTestsInSeparateProcesses]
class ChatCompletionLogRepositoryTest extends AbstractIntegrationTestCase
{
    private ChatCompletionLogRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = $this->getService(ChatCompletionLogRepository::class);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(ChatCompletionLogRepository::class, $this->repository);
    }

    public function testCount(): void
    {
        $count = $this->repository->count([]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testFindRecent(): void
    {
        $results = $this->repository->findRecent(10);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(10, count($results));
    }

    public function testFindRecentWithDefaultLimit(): void
    {
        $results = $this->repository->findRecent();
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(20, count($results));
    }
}
