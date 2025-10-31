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
use Tourze\SiliconFlowBundle\Entity\SiliconFlowVideoGeneration;

/**
 * SiliconFlowVideoGeneration 数据填充
 */
#[When(env: 'dev')]
#[When(env: 'test')]
class SiliconFlowVideoGenerationFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const DEFAULT_VIDEO_GENERATION_REFERENCE = 'default-video-generation';

    public function load(ObjectManager $manager): void
    {
        /** @var BizUser $adminUser */
        $adminUser = $this->getReference(BizUserFixtures::ADMIN_USER_REFERENCE, BizUser::class);

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
