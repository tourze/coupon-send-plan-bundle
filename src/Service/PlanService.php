<?php

namespace Tourze\CouponSendPlanBundle\Service;

use Psr\Log\LoggerInterface;
use Tourze\CouponCoreBundle\Service\CouponService;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\Symfony\Async\Attribute\Async;

class PlanService
{
    public function __construct(private readonly CouponService $couponService, private readonly LoggerInterface $logger)
    {
    }

    #[Async]
    public function send(SendPlan $plan): void
    {
        // 发送人数 x 优惠券数量 = 总发放数量
        foreach ($plan->getCoupons() as $coupon) {
            if (!$coupon->isValid()) {
                $this->logger->warning('优惠券无效，不允许进行发送', [
                    'coupon' => $coupon,
                    'plan' => $plan,
                ]);
                continue;
            }

            foreach ($plan->getUsers() as $user) {
                try {
                    $this->couponService->sendCode($user, $coupon);
                } catch (\Throwable $exception) {
                    if (!($exception instanceof ApiException)) {
                        $this->logger->error('定时发送优惠券失败', [
                            'exception' => $exception,
                            'user' => $user,
                            'coupon' => $coupon,
                        ]);
                    }
                }
            }
        }
    }
}
