<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\SymfonyDependencyServiceLoader\SymfonyDependencyServiceLoaderBundle;

final class SiliconFlowBundle extends Bundle implements BundleDependencyInterface
{
    /**
     * @return array<class-string<Bundle>>
     */
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class,
            SymfonyDependencyServiceLoaderBundle::class,
        ];
    }
}
