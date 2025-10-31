<?php

declare(strict_types=1);

namespace Tourze\CouponSendPlanBundle\Tests\Command;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\CouponSendPlanBundle\Command\SendCouponPlanCommand;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;
use Tourze\CouponSendPlanBundle\Repository\SendPlanRepository;
use Tourze\CouponSendPlanBundle\Service\PlanService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * SendCouponPlanCommand 单元测试
 *
 * @internal
 */
#[CoversClass(SendCouponPlanCommand::class)]
#[RunTestsInSeparateProcesses]
final class SendCouponPlanCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // 设置测试环境
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(SendCouponPlanCommand::class);

        return new CommandTester($command);
    }

    public function testCommandIsInstantiable(): void
    {
        $command = self::getService(SendCouponPlanCommand::class);
        $this->assertInstanceOf(SendCouponPlanCommand::class, $command);
    }

    public function testExecuteReturnsFailureWhenNoPlansFound(): void
    {
        $commandTester = $this->getCommandTester();
        $exitCode = $commandTester->execute([]);

        $this->assertSame(Command::FAILURE, $exitCode);
    }

    public function testExecuteReturnsSuccessWhenPlansFound(): void
    {
        // 创建一个测试计划，设置发送时间为当前时间
        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(CarbonImmutable::now());
        $sendPlan->setRemark('Test plan');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($sendPlan);
        $entityManager->flush();

        $commandTester = $this->getCommandTester();
        $exitCode = $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
    }
}
