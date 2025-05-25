<?php

namespace Tourze\CouponSendPlanBundle\Command;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\CouponSendPlanBundle\Repository\SendPlanRepository;
use Tourze\CouponSendPlanBundle\Service\PlanService;

/**
 * 自动切换优惠券码的状态
 *
 * 建议一分钟跑一次，通过这个，我们可以做延迟的优惠券发送逻辑
 */
#[AsCommand(name: 'coupon:send-plan', description: '自动发送优惠券计划数据')]
class SendCouponPlanCommand extends Command
{
    public function __construct(
        private readonly SendPlanRepository $sendPlanRepository,
        private readonly PlanService $planService,
        private readonly EntityManagerInterface $entityManager,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $plans = $this->sendPlanRepository->findBy([
            'sendTime' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
        if (empty($plans)) {
            return Command::FAILURE;
        }

        foreach ($plans as $plan) {
            // 因为数量可能很大，所以这里不执行具体发送逻辑
            $this->planService->send($plan);
            $plan->setFinished(true);
            $this->entityManager->persist($plan);
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
