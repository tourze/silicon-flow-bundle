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
use Tourze\SiliconFlowBundle\Entity\SiliconFlowImageGeneration;

/**
 * SiliconFlowImageGeneration 数据填充
 */
#[When(env: 'dev')]
#[When(env: 'test')]
class SiliconFlowImageGenerationFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const DEFAULT_IMAGE_GENERATION_REFERENCE = 'default-image-generation';

    public function load(ObjectManager $manager): void
    {
        /** @var BizUser $adminUser */
        $adminUser = $this->getReference(BizUserFixtures::ADMIN_USER_REFERENCE, BizUser::class);

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
