<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\DataFixtures;

use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\UserServiceContracts\UserManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowVideoGeneration;

/**
 * SiliconFlowVideoGeneration 数据填充
 */
#[When(env: 'dev')]
#[When(env: 'test')]
class SiliconFlowVideoGenerationFixtures extends Fixture implements FixtureGroupInterface
{
    public const DEFAULT_VIDEO_GENERATION_REFERENCE = 'default-video-generation';

    public function __construct(
        private readonly UserManagerInterface $userManager,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // 获取或创建测试用户
        $adminUser = $this->getOrCreateTestUser();

        $videoGeneration = new SiliconFlowVideoGeneration();
        $videoGeneration->setModel('Wan-AI/Wan2.2-T2V-A14B');
        $videoGeneration->setPrompt('一只热气球在日出时升上高空');
        $videoGeneration->setNegativePrompt('模糊, 低质量');
        $videoGeneration->setImageSize('1280x720');
        $videoGeneration->setSeed(321);
        $videoGeneration->setRequestPayload([
            'model' => 'Wan-AI/Wan2.2-T2V-A14B',
            'prompt' => '一只热气球在日出时升上高空',
            'image_size' => '1280x720',
        ]);
        $videoGeneration->setSender($adminUser);
        $videoGeneration->setStatus('submitted');

        $manager->persist($videoGeneration);
        $manager->flush();

        $this->addReference(self::DEFAULT_VIDEO_GENERATION_REFERENCE, $videoGeneration);
    }

    private function getOrCreateTestUser(): UserInterface
    {
        // 尝试加载已存在的用户
        $user = $this->userManager->loadUserByIdentifier('test-video-generation-user');

        // 如果用户不存在，创建一个新的测试用户
        if (null === $user) {
            $user = $this->userManager->createUser('test-video-generation-user', 'SiliconFlow视频生成测试用户');
            $this->userManager->saveUser($user);
        }

        return $user;
    }

    public static function getGroups(): array
    {
        return ['silicon_flow'];
    }
}
