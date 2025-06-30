<?php

namespace Tourze\CouponSendPlanBundle\Tests\Command;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\CouponSendPlanBundle\Command\SendCouponPlanCommand;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;
use Tourze\CouponSendPlanBundle\Repository\SendPlanRepository;
use Tourze\CouponSendPlanBundle\Service\PlanService;

class SendCouponPlanCommandTest extends TestCase
{
    private SendPlanRepository $sendPlanRepository;
    private PlanService $planService;
    private EntityManagerInterface $entityManager;
    private SendCouponPlanCommand $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->sendPlanRepository = $this->createMock(SendPlanRepository::class);
        $this->planService = $this->createMock(PlanService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->command = new SendCouponPlanCommand(
            $this->sendPlanRepository,
            $this->planService,
            $this->entityManager
        );

        $application = new Application();
        $application->add($this->command);

        $command = $application->find('coupon:send-plan');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithNoPlans(): void
    {
        // 模拟没有找到任何计划
        $this->sendPlanRepository->expects($this->once())
            ->method('findBy')
            ->with($this->callback(function ($criteria) {
                // 验证包含 sendTime 键
                $this->assertArrayHasKey('sendTime', $criteria);
                // 验证时间格式
                $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $criteria['sendTime']);
                return true;
            }))
            ->willReturn([]);

        // 执行命令
        $exitCode = $this->commandTester->execute([]);

        // 验证返回失败
        $this->assertEquals(1, $exitCode);
    }

    public function testExecuteWithPlans(): void
    {
        // 创建模拟的发送计划
        $plan1 = $this->createMock(SendPlan::class);
        $plan2 = $this->createMock(SendPlan::class);

        // 模拟找到计划
        $this->sendPlanRepository->expects($this->once())
            ->method('findBy')
            ->willReturn([$plan1, $plan2]);

        // 期望调用发送服务
        $this->planService->expects($this->exactly(2))
            ->method('send')
            ->willReturnCallback(function ($plan) use ($plan1, $plan2) {
                $this->assertContains($plan, [$plan1, $plan2]);
            });

        // 期望设置完成状态
        $plan1->expects($this->once())
            ->method('setFinished')
            ->with(true);
        $plan2->expects($this->once())
            ->method('setFinished')
            ->with(true);

        // 期望持久化和刷新
        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(function ($plan) use ($plan1, $plan2) {
                $this->assertContains($plan, [$plan1, $plan2]);
            });
        
        $this->entityManager->expects($this->exactly(2))
            ->method('flush');

        // 执行命令
        $exitCode = $this->commandTester->execute([]);

        // 验证返回成功
        $this->assertEquals(0, $exitCode);
    }

    public function testCommandConfiguration(): void
    {
        $this->assertEquals('coupon:send-plan', $this->command->getName());
        $this->assertEquals('自动发送优惠券计划数据', $this->command->getDescription());
    }
}