<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\SiliconFlowBundle\Controller\Admin\ChatCompletionLogCrudController;
use Tourze\SiliconFlowBundle\Entity\ChatCompletionLog;

/**
 * SiliconFlow 聊天完成日志 CRUD 控制器测试
 */
#[CoversClass(ChatCompletionLogCrudController::class)]
#[RunTestsInSeparateProcesses]
class ChatCompletionLogCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            '请求 ID' => ['请求 ID'],
            '模型' => ['模型'],
            '状态' => ['状态'],
            'Prompt Tokens' => ['Prompt Tokens'],
            'Completion Tokens' => ['Completion Tokens'],
            '总 Tokens' => ['总 Tokens'],
            '创建时间' => ['创建时间'],
        ];
    }

    /**
     * @return \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController<ChatCompletionLog>
     */
    protected function getControllerService(): \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController
    {
        return self::getService(ChatCompletionLogCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        return [
            '请求 ID' => ['请求 ID'],
            '模型' => ['模型'],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        return [
            '请求 ID' => ['请求 ID'],
            '模型' => ['模型'],
            '状态' => ['状态'],
        ];
    }

    /**
     * 测试获取实体类名
     */
    public function testGetEntityFqcn(): void
    {
        $fqcn = ChatCompletionLogCrudController::getEntityFqcn();
        self::assertSame(ChatCompletionLog::class, $fqcn);
    }
}
