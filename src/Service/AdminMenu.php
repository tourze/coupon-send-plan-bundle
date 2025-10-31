<?php

declare(strict_types=1);

namespace Tourze\CouponSendPlanBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

/**
 * 优惠券发送计划管理菜单服务
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('优惠券发送计划')) {
            $item->addChild('优惠券发送计划');
        }

        $sendPlanMenu = $item->getChild('优惠券发送计划');
        if (null === $sendPlanMenu) {
            return;
        }

        $sendPlanMenu->addChild('发送计划')
            ->setUri($this->linkGenerator->getCurdListPage(SendPlan::class))
            ->setAttribute('icon', 'fas fa-paper-plane')
        ;
    }
}
