<?php

namespace Tourze\CouponSendPlanBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CouponCoreBundle\DataFixtures\CouponFixtures;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;

/**
 * 发送计划数据填充
 * 创建测试用的发送计划数据，关联优惠券
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class SendPlanFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    // 发送计划引用常量
    public const SEND_PLAN_CLOTHES = 'send-plan-clothes';
    public const SEND_PLAN_ELECTRONICS = 'send-plan-electronics';
    public const SEND_PLAN_RESTAURANT = 'send-plan-restaurant';

    public function load(ObjectManager $manager): void
    {
        // 获取优惠券引用
        $basicDiscountCoupon = $this->getReference(CouponFixtures::COUPON_BASIC_DISCOUNT, Coupon::class);
        $shortTermCoupon = $this->getReference(CouponFixtures::COUPON_SHORT_TERM, Coupon::class);
        $needActiveCoupon = $this->getReference(CouponFixtures::COUPON_NEED_ACTIVE, Coupon::class);

        // 创建服装类优惠券发送计划
        $sendPlan1 = new SendPlan();
        $sendPlan1->setRemark('服装类优惠券发送计划');
        $sendPlan1->setSendTime(new \DateTimeImmutable('2024-01-01 09:00:00'));
        $sendPlan1->setFinished(false);
        $sendPlan1->addCoupon($basicDiscountCoupon);
        $manager->persist($sendPlan1);

        // 创建数码产品优惠券发送计划
        $sendPlan2 = new SendPlan();
        $sendPlan2->setRemark('数码产品优惠券发送计划');
        $sendPlan2->setSendTime(new \DateTimeImmutable('2024-01-15 14:30:00'));
        $sendPlan2->setFinished(true);
        $sendPlan2->addCoupon($shortTermCoupon);
        $manager->persist($sendPlan2);

        // 创建餐厅类优惠券发送计划
        $sendPlan3 = new SendPlan();
        $sendPlan3->setRemark('餐厅类优惠券发送计划');
        $sendPlan3->setSendTime(new \DateTimeImmutable('2024-02-01 10:00:00'));
        $sendPlan3->setFinished(false);
        $sendPlan3->addCoupon($needActiveCoupon);
        $manager->persist($sendPlan3);

        $manager->flush();

        // 添加引用供其他 Fixture 使用
        $this->addReference(self::SEND_PLAN_CLOTHES, $sendPlan1);
        $this->addReference(self::SEND_PLAN_ELECTRONICS, $sendPlan2);
        $this->addReference(self::SEND_PLAN_RESTAURANT, $sendPlan3);
    }

    public function getDependencies(): array
    {
        return [
            CouponFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['send-plan', 'test'];
    }
}
