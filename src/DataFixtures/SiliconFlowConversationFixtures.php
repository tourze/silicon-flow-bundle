<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConversation;
use Tourze\UserServiceContracts\UserManagerInterface;

/**
 * SiliconFlowConversation 数据填充
 */
#[When(env: 'dev')]
#[When(env: 'test')]
class SiliconFlowConversationFixtures extends Fixture implements FixtureGroupInterface
{
    public const DEFAULT_CONVERSATION_REFERENCE = 'default-conversation';

    public function __construct(
        private readonly UserManagerInterface $userManager,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // 获取或创建测试用户
        $user = $this->getOrCreateTestUser();

        $conversation = new SiliconFlowConversation();
        $conversation->setConversationType(SiliconFlowConversation::TYPE_SINGLE);
        $conversation->setModel('deepseek-ai/DeepSeek-V3');
        $conversation->setQuestion('SiliconFlow 提供哪些模型？');
        $conversation->setAnswer('SiliconFlow 提供多种 AI 模型，包括文本生成、图像生成、视频生成等。');
        $conversation->setSender($user);

        $manager->persist($conversation);
        $manager->flush();

        $this->addReference(self::DEFAULT_CONVERSATION_REFERENCE, $conversation);
    }

    private function getOrCreateTestUser(): UserInterface
    {
        // 尝试加载已存在的用户
        $user = $this->userManager->loadUserByIdentifier('test-silicon-flow-user');

        // 如果用户不存在，创建一个新的测试用户
        if (null === $user) {
            $user = $this->userManager->createUser('test-silicon-flow-user', 'SiliconFlow测试用户');
            $this->userManager->saveUser($user);
        }

        return $user;
    }

    public static function getGroups(): array
    {
        return ['silicon_flow'];
    }
}
