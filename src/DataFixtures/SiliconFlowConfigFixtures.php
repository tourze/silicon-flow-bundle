<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowConfigRepository;

#[When(env: 'dev')]
#[When(env: 'test')]
class SiliconFlowConfigFixtures extends Fixture implements FixtureGroupInterface
{
    public const DEFAULT_CONFIG_REFERENCE = 'default-config';

    public function __construct(
        private readonly SiliconFlowConfigRepository $configRepository,
    ) {
    }

    public function load(ObjectManager $manager): void
    {

        $existing = $this->configRepository->findOneBy(['isActive' => true]);
        if (null !== $existing) {
            $this->addReference(self::DEFAULT_CONFIG_REFERENCE, $existing);

            return;
        }

        $config = new SiliconFlowConfig();
        $config->setName('默认配置');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('sk-bxicgznhcwpjannmrtsyzifybhjjtsdeczojfthvtsydfgmx');
        $config->setIsActive(true);
        $config->setPriority(100);
        $config->setDescription('由数据Fixtures自动创建的默认配置');

        $manager->persist($config);
        $manager->flush();

        $this->addReference(self::DEFAULT_CONFIG_REFERENCE, $config);
    }

    public static function getGroups(): array
    {
        return ['silicon_flow'];
    }
}

