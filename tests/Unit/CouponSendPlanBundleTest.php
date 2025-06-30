<?php

namespace Tourze\CouponSendPlanBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tourze\CouponSendPlanBundle\CouponSendPlanBundle;

class CouponSendPlanBundleTest extends TestCase
{
    public function testBundleCanBeInstantiated(): void
    {
        $bundle = new CouponSendPlanBundle();
        
        $this->assertInstanceOf(CouponSendPlanBundle::class, $bundle);
    }
}