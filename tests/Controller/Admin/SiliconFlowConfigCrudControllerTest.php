<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\SiliconFlowBundle\Controller\Admin\SiliconFlowConfigCrudController;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;

/**
 * SiliconFlow 配置 CRUD 控制器测试
 */
#[CoversClass(SiliconFlowConfigCrudController::class)]
class SiliconFlowConfigCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            '配置名称' => ['配置名称'],
            'API 基础地址' => ['API 基础地址'],
            '是否启用' => ['是否启用'],
            '优先级' => ['优先级'],
            '备注' => ['备注'],
            '创建时间' => ['创建时间'],
            '更新时间' => ['更新时间'],
        ];
    }

    /**
     * @return \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController<SiliconFlowConfig>
     */
    protected function getControllerService(): \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController
    {
        return self::getService(SiliconFlowConfigCrudController::class);
    }

    /**
     * 测试获取实体类名
     */
    public function testGetEntityFqcn(): void
    {
        $fqcn = SiliconFlowConfigCrudController::getEntityFqcn();
        $this->assertSame(SiliconFlowConfig::class, $fqcn);
    }
}
