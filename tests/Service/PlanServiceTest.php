<?php

namespace Tourze\CouponSendPlanBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;
use Tourze\CouponSendPlanBundle\Service\PlanService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * PlanService 集成测试
 *
 * @internal
 */
#[CoversClass(PlanService::class)]
#[RunTestsInSeparateProcesses]
final class PlanServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试基类要求的设置方法
    }

    public function testServiceIsInstantiable(): void
    {
        $planService = self::getService(PlanService::class);
        $this->assertInstanceOf(PlanService::class, $planService);
    }

    public function testSendWithNoCoupons(): void
    {
        $planService = self::getService(PlanService::class);

        // 创建一个真实的 SendPlan 实体，不包含优惠券
        $plan = new SendPlan();
        $plan->setSendTime(new \DateTimeImmutable('+1 day'));
        $plan->setRemark('Test Plan with no coupons');

        // 测试发送计划时，由于没有优惠券，应该不会产生任何错误
        $planService->send($plan);

        // 由于是集成测试，我们验证服务能正常工作即可
        $this->assertTrue(true, 'Service should handle empty coupon list without errors');
    }
}
