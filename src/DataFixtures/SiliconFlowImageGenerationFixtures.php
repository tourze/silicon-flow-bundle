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
use Tourze\SiliconFlowBundle\Entity\SiliconFlowImageGeneration;

/**
 * SiliconFlowImageGeneration 数据填充
 */
#[When(env: 'dev')]
#[When(env: 'test')]
class SiliconFlowImageGenerationFixtures extends Fixture implements FixtureGroupInterface
{
    public const DEFAULT_IMAGE_GENERATION_REFERENCE = 'default-image-generation';

    public function __construct(
        private readonly UserManagerInterface $userManager,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // 获取或创建测试用户
        $adminUser = $this->getOrCreateTestUser();

        $imageGeneration = new SiliconFlowImageGeneration();
        $imageGeneration->setModel('Kwai-Kolors/Kolors');
        $imageGeneration->setPrompt('一只猫在沙发上睡觉');
        $imageGeneration->setNegativePrompt('模糊, 低质量');
        $imageGeneration->setImageSize('1024x1024');
        $imageGeneration->setBatchSize(1);
        $imageGeneration->setRequestPayload([
            'model' => 'Kwai-Kolors/Kolors',
            'prompt' => '一只猫在沙发上睡觉',
            'image_size' => '1024x1024',
        ]);
        $imageGeneration->setSender($adminUser);
        $imageGeneration->setStatus('completed');

        $manager->persist($imageGeneration);
        $manager->flush();

        $this->addReference(self::DEFAULT_IMAGE_GENERATION_REFERENCE, $imageGeneration);
    }

    private function getOrCreateTestUser(): UserInterface
    {
        // 尝试加载已存在的用户
        $user = $this->userManager->loadUserByIdentifier('test-image-generation-user');

        // 如果用户不存在，创建一个新的测试用户
        if (null === $user) {
            $user = $this->userManager->createUser('test-image-generation-user', 'SiliconFlow图片生成测试用户');
            $this->userManager->saveUser($user);
        }

        return $user;
    }

    public static function getGroups(): array
    {
        return ['silicon_flow'];
    }
}
