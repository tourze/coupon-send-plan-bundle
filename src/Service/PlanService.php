<?php

namespace Tourze\CouponSendPlanBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Service\CouponService;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\Symfony\AopAsyncBundle\Attribute\Async;

#[WithMonologChannel(channel: 'coupon_send_plan')]
class PlanService
{
    public function __construct(private CouponService $couponService, private LoggerInterface $logger)
    {
    }

    #[Async]
    public function send(SendPlan $plan): void
    {
        // 发送人数 x 优惠券数量 = 总发放数量
        foreach ($plan->getCoupons() as $coupon) {
            $this->processCouponSending($plan, $coupon);
        }
    }

    private function processCouponSending(SendPlan $plan, Coupon $coupon): void
    {
        if (true !== $coupon->isValid()) {
            $this->logger->warning('优惠券无效，不允许进行发送', [
                'coupon' => $coupon,
                'plan' => $plan,
            ]);

            return;
        }

        foreach ($plan->getUsers() as $user) {
            $this->sendCouponToUser($user, $coupon);
        }
    }

    private function sendCouponToUser(UserInterface $user, Coupon $coupon): void
    {
        try {
            $this->couponService->sendCode($user, $coupon);
        } catch (\Throwable $exception) {
            $this->handleSendingError($exception, $user, $coupon);
        }
    }

    private function handleSendingError(\Throwable $exception, UserInterface $user, Coupon $coupon): void
    {
        if (!($exception instanceof ApiException)) {
            $this->logger->error('定时发送优惠券失败', [
                'exception' => $exception,
                'user' => $user,
                'coupon' => $coupon,
            ]);
        }
    }
}
