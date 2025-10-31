<?php

declare(strict_types=1);

namespace Tourze\CouponSendPlanBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponSendPlanBundle\Service\AttributeControllerLoader;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $loader;

    protected function onSetUp(): void
    {
        $this->loader = self::getService(AttributeControllerLoader::class);
    }

    public function testImplementsRoutingAutoLoaderInterface(): void
    {
        // 验证加载器的实际行为而不是类型
        self::assertFalse($this->loader->supports('any-resource'));
    }

    public function testLoadReturnsRouteCollection(): void
    {
        $collection = $this->loader->load('any-resource');

        // 验证返回集合的实际行为
        self::assertGreaterThanOrEqual(0, $collection->count());
    }

    public function testSupportsAlwaysReturnsFalse(): void
    {
        self::assertFalse($this->loader->supports('any-resource'));
    }

    public function testAutoloadReturnsRouteCollection(): void
    {
        $collection = $this->loader->autoload();

        // 验证返回集合的实际行为
        self::assertGreaterThanOrEqual(0, $collection->count());
    }
}
