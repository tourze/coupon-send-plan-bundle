# 优惠券发送计划包

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/coupon-send-plan-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-send-plan-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/coupon-send-plan-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-send-plan-bundle)
[![License](https://img.shields.io/packagist/l/tourze/coupon-send-plan-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-send-plan-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/coupon-send-plan-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-send-plan-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Coverage Status](https://img.shields.io/codecov/c/github/tourze/php-monorepo.svg?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

一个 Symfony 包，提供定时优惠券发送功能，允许您创建计划在指定时间向用户发送优惠券。

## 目录

- [功能特性](#功能特性)
- [依赖关系](#依赖关系)
- [安装](#安装)
- [配置](#配置)
- [快速开始](#快速开始)
  - [1. 启用包](#1-启用包)
  - [2. 创建发送计划](#2-创建发送计划)
  - [3. 执行发送计划](#3-执行发送计划)
- [控制台命令](#控制台命令)
  - [`coupon:send-plan`](#couponsend-plan)
- [高级用法](#高级用法)
  - [自定义计划处理](#自定义计划处理)
  - [批量处理](#批量处理)
  - [错误处理和监控](#错误处理和监控)
- [API 参考](#api-参考)
  - [SendPlan 实体](#sendplan-实体)
  - [PlanService](#planservice)
- [贡献](#贡献)
- [许可证](#许可证)

## 功能特性

- 创建带有定时执行时间的优惠券发送计划
- 批量向多个用户发送优惠券
- 支持异步处理
- 全面的日志记录和错误处理
- 与 Doctrine ORM 集成
- 内置控制台命令用于自动执行

## 依赖关系

此包需要以下依赖：

- PHP 8.1 或更高版本
- Symfony 7.3 或更高版本
- Doctrine ORM 3.0 或更高版本
- tourze/coupon-core-bundle
- tourze/symfony-aop-async-bundle

完整的依赖列表请查看 `composer.json`。

## 安装

```bash
composer require tourze/coupon-send-plan-bundle
```

## 配置

无需额外配置。该包使用默认的 Doctrine 实体管理器，并与现有的优惠券系统集成。

## 快速开始

### 1. 启用包

将包添加到您的 `config/bundles.php`：

```php
<?php

return [
    // ... 其他包
    Tourze\CouponSendPlanBundle\CouponSendPlanBundle::class => ['all' => true],
];
```

### 2. 创建发送计划

```php
<?php

use Doctrine\ORM\EntityManagerInterface;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Symfony\Component\Security\Core\User\UserInterface;

// 创建新的发送计划
$sendPlan = new SendPlan();
$sendPlan->setSendTime(new \DateTimeImmutable('+1 hour'));
$sendPlan->setRemark('圣诞促销优惠券');

// 添加优惠券到计划
$sendPlan->addCoupon($coupon1);
$sendPlan->addCoupon($coupon2);

// 添加接收优惠券的用户
$sendPlan->addUser($user1);
$sendPlan->addUser($user2);

// 保存计划
$entityManager->persist($sendPlan);
$entityManager->flush();
```

### 3. 执行发送计划

运行控制台命令来处理待发送的计划：

```bash
php bin/console coupon:send-plan
```

此命令应该定期运行（例如通过 cron 任务），以自动处理定时发送计划。

## 控制台命令

### `coupon:send-plan`

自动发送在当前时间安排的优惠券计划。

**用法：**
```bash
php bin/console coupon:send-plan
```

**描述：**
- 查找所有安排在精确当前时间的发送计划（格式为 'Y-m-d H:i:s'）
- 通过向所有指定用户发送优惠券来处理每个计划
- 将完成的计划标记为已完成
- 支持异步处理以获得更好的性能

**推荐设置：**
将此命令添加到您的 cron 任务中，每分钟运行一次：
```bash
* * * * * php /path/to/your/project/bin/console coupon:send-plan
```

## 高级用法

### 自定义计划处理

您可以通过扩展 `PlanService` 创建自定义的计划处理逻辑：

```php
<?php

use Tourze\CouponSendPlanBundle\Service\PlanService;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;

class CustomPlanService extends PlanService
{
    public function send(SendPlan $plan): void
    {
        // 自定义预处理逻辑
        $this->validatePlan($plan);
        
        // 调用父类实现
        parent::send($plan);
        
        // 自定义后处理逻辑
        $this->notifyAdministrators($plan);
    }
    
    private function validatePlan(SendPlan $plan): void
    {
        // 自定义验证逻辑
    }
    
    private function notifyAdministrators(SendPlan $plan): void
    {
        // 自定义通知逻辑
    }
}
```

### 批量处理

对于大规模优惠券分发，考虑实现批量处理：

```php
<?php

use Tourze\CouponSendPlanBundle\Repository\SendPlanRepository;

// 批量处理计划
$repository = $entityManager->getRepository(SendPlan::class);
$pendingPlans = $repository->findBy(['finished' => false], null, $batchSize = 100);

foreach ($pendingPlans as $plan) {
    $planService->send($plan);
    $plan->setFinished(true);
    
    // 每 10 个计划刷新一次以管理内存
    if (($processedCount % 10) === 0) {
        $entityManager->flush();
    }
}

$entityManager->flush();
```

### 错误处理和监控

实现全面的错误处理和监控：

```php
<?php

use Psr\Log\LoggerInterface;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;

class MonitoredPlanService extends PlanService
{
    public function __construct(
        CouponService $couponService,
        LoggerInterface $logger,
        private MetricsCollector $metricsCollector
    ) {
        parent::__construct($couponService, $logger);
    }
    
    public function send(SendPlan $plan): void
    {
        $startTime = microtime(true);
        
        try {
            parent::send($plan);
            $this->metricsCollector->increment('coupon_plans.success');
        } catch (\Throwable $exception) {
            $this->metricsCollector->increment('coupon_plans.failure');
            throw $exception;
        } finally {
            $duration = microtime(true) - $startTime;
            $this->metricsCollector->timing('coupon_plans.duration', $duration);
        }
    }
}
```

## API 参考

### SendPlan 实体

`SendPlan` 实体表示一个定时优惠券发送任务。

**属性：**
- `sendTime`: `\DateTimeImmutable` - 何时执行计划
- `remark`: `string|null` - 计划的可选描述
- `finished`: `bool` - 计划是否已执行
- `coupons`: `Collection<Coupon>` - 要发送的优惠券
- `users`: `Collection<UserInterface>` - 接收优惠券的用户

**方法：**
- `addCoupon(Coupon $coupon)` - 向计划添加优惠券
- `addUser(UserInterface $user)` - 添加接收优惠券的用户
- `setFinished(bool $finished)` - 将计划标记为完成/未完成

### PlanService

`PlanService` 处理根据计划实际发送优惠券。

**方法：**
- `send(SendPlan $plan)` - 执行发送计划（异步）

## 贡献

详细信息请参见 [CONTRIBUTING.md](CONTRIBUTING.md)。

## 许可证

MIT 许可证 (MIT)。更多信息请参见 [License File](LICENSE)。