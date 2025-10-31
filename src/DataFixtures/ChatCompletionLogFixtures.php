<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\SiliconFlowBundle\Entity\ChatCompletionLog;

/**
 * ChatCompletionLog 数据填充
 */
#[When(env: 'dev')]
#[When(env: 'test')]
class ChatCompletionLogFixtures extends Fixture implements FixtureGroupInterface
{
    public const DEFAULT_LOG_REFERENCE = 'default-log';

    public function load(ObjectManager $manager): void
    {
        $log = new ChatCompletionLog();
        $log->setModel('deepseek-ai/DeepSeek-V3');
        $log->setStatus('success');
        $log->setRequestPayload([
            'model' => 'deepseek-ai/DeepSeek-V3',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello'],
            ],
        ]);
        $log->setResponsePayload([
            'id' => 'test-id-1',
            'choices' => [
                ['message' => ['content' => 'Hi there!']],
            ],
        ]);
        $log->setPromptTokens(10);
        $log->setCompletionTokens(5);
        $log->setTotalTokens(15);

        $manager->persist($log);
        $manager->flush();

        $this->addReference(self::DEFAULT_LOG_REFERENCE, $log);
    }

    public static function getGroups(): array
    {
        return ['silicon_flow'];
    }
}
