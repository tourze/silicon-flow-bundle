<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use Tourze\SiliconFlowBundle\Service\AdminMenu;

/**
 * SiliconFlow 管理菜单服务测试
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private LinkGeneratorInterface&MockObject $linkGenerator;
    private ItemInterface&MockObject $menu;
    private ItemInterface&MockObject $subMenu;
    private AdminMenu $adminMenu;

    protected function onSetUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $this->menu = $this->createMock(ItemInterface::class);
        $this->subMenu = $this->createMock(ItemInterface::class);

        // 将 mock 服务注入容器
        self::getContainer()->set(LinkGeneratorInterface::class, $this->linkGenerator);

        // 从容器获取服务实例
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    /**
     * 测试菜单生成功能
     */
    public function testInvoke(): void
    {
        // 设置菜单生成器的预期调用
        $this->linkGenerator
            ->expects($this->exactly(6))
            ->method('getCurdListPage')
            ->willReturn('/admin/list');

        // 设置菜单项的预期调用
        $this->menu
            ->expects($this->once())
            ->method('addChild')
            ->with('SiliconFlow 管理', ['icon' => 'fas fa-robot'])
            ->willReturn($this->subMenu);

        $this->subMenu
            ->expects($this->exactly(6))
            ->method('addChild')
            ->willReturn($this->createMock(ItemInterface::class));

        // 执行菜单生成
        ($this->adminMenu)($this->menu);
    }

    /**
     * 测试服务实例化
     */
    public function testServiceInstantiation(): void
    {
        self::assertInstanceOf(AdminMenu::class, $this->adminMenu);
    }
}