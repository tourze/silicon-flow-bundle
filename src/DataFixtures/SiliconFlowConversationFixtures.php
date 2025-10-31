<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\DataFixtures;

use BizUserBundle\DataFixtures\BizUserFixtures;
use BizUserBundle\Entity\BizUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConversation;

/**
 * SiliconFlowConversation 数据填充
 */
#[When(env: 'dev')]
#[When(env: 'test')]
class SiliconFlowConversationFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const DEFAULT_CONVERSATION_REFERENCE = 'default-conversation';

    public function load(ObjectManager $manager): void
    {
        /** @var BizUser $adminUser */
        $adminUser = $this->getReference(BizUserFixtures::ADMIN_USER_REFERENCE, BizUser::class);

        $conversation = new SiliconFlowConversation();
        $conversation->setConversationType(SiliconFlowConversation::TYPE_SINGLE);
        $conversation->setModel('deepseek-ai/DeepSeek-V3');
        $conversation->setQuestion('SiliconFlow 提供哪些模型？');
        $conversation->setAnswer('SiliconFlow 提供多种 AI 模型，包括文本生成、图像生成、视频生成等。');
        $conversation->setSender($adminUser);

        $manager->persist($conversation);
        $manager->flush();

        $this->addReference(self::DEFAULT_CONVERSATION_REFERENCE, $conversation);
    }

    public function getDependencies(): array
    {
        return [
            BizUserFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['silicon_flow'];
    }
}
