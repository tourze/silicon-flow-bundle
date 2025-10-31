<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\SiliconFlowBundle\Controller\Admin\SiliconFlowConversationCrudController;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConversation;

/**
 * SiliconFlow 对话 CRUD 控制器测试
 */
#[CoversClass(SiliconFlowConversationCrudController::class)]
class SiliconFlowConversationCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            '对话类型' => ['对话类型'],
            '使用模型' => ['使用模型'],
            '问题内容' => ['问题内容'],
            '返回答案' => ['返回答案'],
            '发送者' => ['发送者'],
            '创建时间' => ['创建时间'],
            '更新时间' => ['更新时间'],
        ];
    }

    /**
     * @return \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController<SiliconFlowConversation>
     */
    protected function getControllerService(): \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController
    {
        return self::getService(SiliconFlowConversationCrudController::class);
    }

    /**
     * 测试获取实体类名
     */
    public function testGetEntityFqcn(): void
    {
        $fqcn = SiliconFlowConversationCrudController::getEntityFqcn();
        $this->assertSame(SiliconFlowConversation::class, $fqcn);
    }
}
