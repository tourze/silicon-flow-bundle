<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;

/**
 * SiliconFlow 配置实体测试
 */
#[CoversClass(SiliconFlowConfig::class)]
class SiliconFlowConfigTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new SiliconFlowConfig();
    }
}