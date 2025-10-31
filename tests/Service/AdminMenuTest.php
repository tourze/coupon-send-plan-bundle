<?php

declare(strict_types=1);

namespace Tourze\CouponSendPlanBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponSendPlanBundle\Service\AdminMenu;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // No additional setup needed
    }

    public function testCanBeInstantiated(): void
    {
        $adminMenu = self::getService(AdminMenu::class);

        // 烟雾测试：验证对象确实被创建（通过访问属性验证）
        $reflection = new \ReflectionClass($adminMenu);
        self::assertTrue($reflection->hasProperty('linkGenerator'));
    }

    public function testInvokeWithNewMenuStructure(): void
    {
        // 从容器获取AdminMenu服务
        $adminMenu = self::getService(AdminMenu::class);
        $rootItem = $this->createMock(ItemInterface::class);
        $sendPlanMenuMock = $this->createMock(ItemInterface::class);
        $childMenuItemMock = $this->createMock(ItemInterface::class);

        // 期望检查两次是否存在子菜单
        $rootItem->expects(self::exactly(2))
            ->method('getChild')
            ->with('优惠券发送计划')
            ->willReturnOnConsecutiveCalls(null, $sendPlanMenuMock)
        ;

        // 期望添加子菜单
        $rootItem->expects(self::once())
            ->method('addChild')
            ->with('优惠券发送计划')
            ->willReturn($sendPlanMenuMock)
        ;

        // 期望添加菜单项并返回子菜单项（用于链式调用）
        $sendPlanMenuMock->expects(self::once())
            ->method('addChild')
            ->with('发送计划')
            ->willReturn($childMenuItemMock)
        ;

        // 期望链式调用：setUri 和 setAttribute
        $childMenuItemMock->expects(self::once())
            ->method('setUri')
            ->willReturn($childMenuItemMock)  // 返回自己以支持链式调用
        ;

        $childMenuItemMock->expects(self::once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-paper-plane')
            ->willReturn($childMenuItemMock)
        ;

        // 调用方法
        ($adminMenu)($rootItem);
    }
}
