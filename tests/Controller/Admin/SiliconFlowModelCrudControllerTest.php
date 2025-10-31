<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\SiliconFlowBundle\Controller\Admin\SiliconFlowModelCrudController;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowModel;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * SiliconFlow 模型 CRUD 控制器测试
 */
#[CoversClass(SiliconFlowModelCrudController::class)]
#[RunTestsInSeparateProcesses]
class SiliconFlowModelCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            '模型标识' => ['模型标识'],
            '对象类型' => ['对象类型'],
            '是否有效' => ['是否有效'],
            '创建时间' => ['创建时间'],
            '更新时间' => ['更新时间'],
        ];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'modelId' => ['modelId'];
        yield 'objectType' => ['objectType'];
        yield 'isActive' => ['isActive'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        return self::provideNewPageFields();
    }

    /**
     * @return AbstractCrudController<SiliconFlowModel>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(SiliconFlowModelCrudController::class);
    }
}