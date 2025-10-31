<?php

declare(strict_types=1);

namespace Tourze\CouponSendPlanBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponSendPlanBundle\CouponSendPlanBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(CouponSendPlanBundle::class)]
#[RunTestsInSeparateProcesses]
final class CouponSendPlanBundleTest extends AbstractBundleTestCase
{
}
