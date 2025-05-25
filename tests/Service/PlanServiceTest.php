<?php

namespace Tourze\CouponSendPlanBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Service\CouponService;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;
use Tourze\CouponSendPlanBundle\Service\PlanService;
use Tourze\JsonRPC\Core\Exception\ApiException;

class PlanServiceTest extends TestCase
{
    private PlanService $planService;
    private CouponService $couponService;
    private LoggerInterface $logger;
    
    protected function setUp(): void
    {
        $this->couponService = $this->createMock(CouponService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        
        $this->planService = new PlanService(
            $this->couponService,
            $this->logger
        );
    }
    
    public function testServiceCreation(): void
    {
        // 验证服务成功创建
        $this->assertInstanceOf(PlanService::class, $this->planService);
    }
    
    public function testSendWithValidCoupon(): void
    {
        // 创建模拟的发送计划
        $plan = $this->createMock(SendPlan::class);
        
        // 创建有效的优惠券
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        // 创建模拟用户
        $user1 = $this->createMock(UserInterface::class);
        $user2 = $this->createMock(UserInterface::class);
        
        // 设置发送计划包含的优惠券
        $coupons = new ArrayCollection([$coupon]);
        $plan->method('getCoupons')->willReturn($coupons);
        
        // 设置发送计划包含的用户
        $users = new ArrayCollection([$user1, $user2]);
        $plan->method('getUsers')->willReturn($users);
        
        // 设置期望的 couponService->sendCode 调用
        $this->couponService->expects($this->exactly(2))
            ->method('sendCode')
            ->with(
                $this->callback(function($user) use ($user1, $user2) {
                    return $user === $user1 || $user === $user2;
                }),
                $this->identicalTo($coupon),
                $this->equalTo('')
            );
        
        // 执行测试
        $this->planService->send($plan);
    }
    
    public function testSendWithInvalidCoupon(): void
    {
        // 创建模拟的发送计划
        $plan = $this->createMock(SendPlan::class);
        
        // 创建无效的优惠券
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(false);
        
        // 创建模拟用户
        $user = $this->createMock(UserInterface::class);
        
        // 设置发送计划包含的优惠券
        $coupons = new ArrayCollection([$coupon]);
        $plan->method('getCoupons')->willReturn($coupons);
        
        // 设置发送计划包含的用户
        $users = new ArrayCollection([$user]);
        $plan->method('getUsers')->willReturn($users);
        
        // 设置期望的 logger->warning 调用
        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                '优惠券无效，不允许进行发送',
                $this->callback(function ($context) use ($coupon, $plan) {
                    return $context['coupon'] === $coupon && $context['plan'] === $plan;
                })
            );
        
        // couponService->sendCode 不应该被调用
        $this->couponService->expects($this->never())
            ->method('sendCode');
        
        // 执行测试
        $this->planService->send($plan);
    }
    
    public function testSendWithApiException(): void
    {
        // 创建模拟的发送计划
        $plan = $this->createMock(SendPlan::class);
        
        // 创建有效的优惠券
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        // 创建模拟用户
        $user = $this->createMock(UserInterface::class);
        
        // 设置发送计划包含的优惠券
        $coupons = new ArrayCollection([$coupon]);
        $plan->method('getCoupons')->willReturn($coupons);
        
        // 设置发送计划包含的用户
        $users = new ArrayCollection([$user]);
        $plan->method('getUsers')->willReturn($users);
        
        // 模拟 ApiException
        $exception = new ApiException('API异常');
        $this->couponService->method('sendCode')
            ->willThrowException($exception);
        
        // logger->error 不应该被调用（因为是 ApiException）
        $this->logger->expects($this->never())
            ->method('error');
        
        // 执行测试
        $this->planService->send($plan);
    }
    
    public function testSendWithGeneralException(): void
    {
        // 创建模拟的发送计划
        $plan = $this->createMock(SendPlan::class);
        
        // 创建有效的优惠券
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        // 创建模拟用户
        $user = $this->createMock(UserInterface::class);
        
        // 设置发送计划包含的优惠券
        $coupons = new ArrayCollection([$coupon]);
        $plan->method('getCoupons')->willReturn($coupons);
        
        // 设置发送计划包含的用户
        $users = new ArrayCollection([$user]);
        $plan->method('getUsers')->willReturn($users);
        
        // 模拟一般异常
        $exception = new \Exception('一般异常');
        $this->couponService->method('sendCode')
            ->willThrowException($exception);
        
        // 设置期望的 logger->error 调用
        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                '定时发送优惠券失败',
                $this->callback(function ($context) use ($exception, $user, $coupon) {
                    return $context['exception'] === $exception 
                        && $context['user'] === $user 
                        && $context['coupon'] === $coupon;
                })
            );
        
        // 执行测试
        $this->planService->send($plan);
    }
    
    public function testSendWithMultipleCouponsAndUsers(): void
    {
        // 创建模拟的发送计划
        $plan = $this->createMock(SendPlan::class);
        
        // 创建有效的优惠券
        $coupon1 = $this->createMock(Coupon::class);
        $coupon1->method('isValid')->willReturn(true);
        
        $coupon2 = $this->createMock(Coupon::class);
        $coupon2->method('isValid')->willReturn(true);
        
        // 创建模拟用户
        $user1 = $this->createMock(UserInterface::class);
        $user2 = $this->createMock(UserInterface::class);
        
        // 设置发送计划包含的优惠券
        $coupons = new ArrayCollection([$coupon1, $coupon2]);
        $plan->method('getCoupons')->willReturn($coupons);
        
        // 设置发送计划包含的用户
        $users = new ArrayCollection([$user1, $user2]);
        $plan->method('getUsers')->willReturn($users);
        
        // 设置期望的 couponService->sendCode 调用（4次：2个用户 x 2个优惠券）
        $this->couponService->expects($this->exactly(4))
            ->method('sendCode');
        
        // 执行测试
        $this->planService->send($plan);
    }
} 