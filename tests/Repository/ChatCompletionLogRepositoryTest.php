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
        $this->repository = self::getService(ChatCompletionLogRepository::class);
    }

    public function testInstantiation(): void
    {
        self::assertInstanceOf(ChatCompletionLogRepository::class, $this->repository);
    }

    public function testCount(): void
    {
        $count = $this->repository->count([]);
        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testFindRecent(): void
    {
        $results = $this->repository->findRecent(10);
        self::assertIsArray($results);
        self::assertLessThanOrEqual(10, count($results));
    }

    public function testFindRecentWithDefaultLimit(): void
    {
        $results = $this->repository->findRecent();
        self::assertIsArray($results);
        self::assertLessThanOrEqual(20, count($results));
    }
}
