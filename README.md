# Coupon Send Plan Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/coupon-send-plan-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-send-plan-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/coupon-send-plan-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-send-plan-bundle)
[![License](https://img.shields.io/packagist/l/tourze/coupon-send-plan-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-send-plan-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/coupon-send-plan-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-send-plan-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Coverage Status](https://img.shields.io/codecov/c/github/tourze/php-monorepo.svg?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

A Symfony bundle that provides scheduled coupon sending functionality, allowing you to create plans to 
send coupons to users at specified times.

## Table of Contents

- [Features](#features)
- [Dependencies](#dependencies)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
  - [1. Enable the Bundle](#1-enable-the-bundle)
  - [2. Create a Send Plan](#2-create-a-send-plan)
  - [3. Execute Send Plans](#3-execute-send-plans)
- [Console Commands](#console-commands)
  - [`coupon:send-plan`](#couponsend-plan)
- [Advanced Usage](#advanced-usage)
  - [Custom Plan Processing](#custom-plan-processing)
  - [Batch Processing](#batch-processing)
  - [Error Handling and Monitoring](#error-handling-and-monitoring)
- [API Reference](#api-reference)
  - [SendPlan Entity](#sendplan-entity)
  - [PlanService](#planservice)
- [Contributing](#contributing)
- [License](#license)

## Features

- Create coupon send plans with scheduled execution times
- Bulk send coupons to multiple users
- Asynchronous processing support
- Comprehensive logging and error handling
- Integration with Doctrine ORM
- Built-in console command for automated execution

## Dependencies

This bundle requires the following packages:

- PHP 8.1 or higher
- Symfony 7.3 or higher
- Doctrine ORM 3.0 or higher
- tourze/coupon-core-bundle
- tourze/symfony-aop-async-bundle

For a complete list of dependencies, see `composer.json`.

## Installation

```bash
composer require tourze/coupon-send-plan-bundle
```

## Configuration

No additional configuration is required. The bundle uses the default Doctrine entity manager and 
integrates with the existing coupon system.

## Quick Start

### 1. Enable the Bundle

Add the bundle to your `config/bundles.php`:

```php
<?php

return [
    // ... other bundles
    Tourze\CouponSendPlanBundle\CouponSendPlanBundle::class => ['all' => true],
];
```

### 2. Create a Send Plan

```php
<?php

use Doctrine\ORM\EntityManagerInterface;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Symfony\Component\Security\Core\User\UserInterface;

// Create a new send plan
$sendPlan = new SendPlan();
$sendPlan->setSendTime(new \DateTimeImmutable('+1 hour'));
$sendPlan->setRemark('Christmas promotion coupons');

// Add coupons to the plan
$sendPlan->addCoupon($coupon1);
$sendPlan->addCoupon($coupon2);

// Add users to receive the coupons
$sendPlan->addUser($user1);
$sendPlan->addUser($user2);

// Save the plan
$entityManager->persist($sendPlan);
$entityManager->flush();
```

### 3. Execute Send Plans

Run the console command to process pending send plans:

```bash
php bin/console coupon:send-plan
```

This command should be run periodically (e.g., via cron job) to automatically process scheduled 
send plans.

## Console Commands

### `coupon:send-plan`

Automatically sends coupon plans that are scheduled for the current time.

**Usage:**
```bash
php bin/console coupon:send-plan
```

**Description:**
- Finds all send plans scheduled for the exact current time (formatted as 'Y-m-d H:i:s')
- Processes each plan by sending coupons to all specified users
- Marks completed plans as finished
- Supports asynchronous processing for better performance

**Recommended Setup:**
Add this command to your cron job to run every minute:
```bash
* * * * * php /path/to/your/project/bin/console coupon:send-plan
```

## Advanced Usage

### Custom Plan Processing

You can create custom logic for processing send plans by extending the `PlanService`:

```php
<?php

use Tourze\CouponSendPlanBundle\Service\PlanService;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;

class CustomPlanService extends PlanService
{
    public function send(SendPlan $plan): void
    {
        // Custom pre-processing logic
        $this->validatePlan($plan);
        
        // Call parent implementation
        parent::send($plan);
        
        // Custom post-processing logic
        $this->notifyAdministrators($plan);
    }
    
    private function validatePlan(SendPlan $plan): void
    {
        // Custom validation logic
    }
    
    private function notifyAdministrators(SendPlan $plan): void
    {
        // Custom notification logic
    }
}
```

### Batch Processing

For large-scale coupon distribution, consider implementing batch processing:

```php
<?php

use Tourze\CouponSendPlanBundle\Repository\SendPlanRepository;

// Process plans in batches
$repository = $entityManager->getRepository(SendPlan::class);
$pendingPlans = $repository->findBy(['finished' => false], null, $batchSize = 100);

foreach ($pendingPlans as $plan) {
    $planService->send($plan);
    $plan->setFinished(true);
    
    // Flush every 10 plans to manage memory
    if (($processedCount % 10) === 0) {
        $entityManager->flush();
    }
}

$entityManager->flush();
```

### Error Handling and Monitoring

Implement comprehensive error handling and monitoring:

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

## API Reference

### SendPlan Entity

The `SendPlan` entity represents a scheduled coupon sending task.

**Properties:**
- `sendTime`: `\DateTimeImmutable` - When to execute the plan
- `remark`: `string|null` - Optional description of the plan
- `finished`: `bool` - Whether the plan has been executed
- `coupons`: `Collection<Coupon>` - Coupons to send
- `users`: `Collection<UserInterface>` - Users to receive coupons

**Methods:**
- `addCoupon(Coupon $coupon)` - Add a coupon to the plan
- `addUser(UserInterface $user)` - Add a user to receive coupons
- `setFinished(bool $finished)` - Mark the plan as finished/unfinished

### PlanService

The `PlanService` handles the actual sending of coupons according to the plan.

**Methods:**
- `send(SendPlan $plan)` - Execute a send plan (asynchronous)

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.