<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\SiliconFlowBundle\Controller\Admin\SiliconFlowVideoGenerationCrudController;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowVideoGeneration;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * SiliconFlow 视频生成 CRUD 控制器测试
 */
#[CoversClass(SiliconFlowVideoGenerationCrudController::class)]
#[RunTestsInSeparateProcesses]
class SiliconFlowVideoGenerationCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            '请求 ID' => ['请求 ID'],
            '模型' => ['模型'],
            '发送者' => ['发送者'],
            '图像尺寸' => ['图像尺寸'],
            '状态' => ['状态'],
            '推理耗时(秒)' => ['推理耗时(秒)'],
            '创建时间' => ['创建时间'],
            '更新时间' => ['更新时间'],
        ];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        // 此控制器禁用了 NEW 动作，但基类需要至少一个字段来通过验证
        // 返回一个虚拟字段以避免空数据集错误
        yield 'model' => ['model'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        // 此控制器禁用了 EDIT 动作，但基类需要至少一个字段来通过验证
        // 返回一个虚拟字段以避免空数据集错误
        yield 'model' => ['model'];
    }

    /**
     * @return AbstractCrudController<SiliconFlowVideoGeneration>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(SiliconFlowVideoGenerationCrudController::class);
    }
}