<?php

namespace Tourze\CouponSendPlanBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\CouponSendPlanBundle\DependencyInjection\CouponSendPlanExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(CouponSendPlanExtension::class)]
final class CouponSendPlanExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // 集成测试不需要额外的设置
    }

    private function createExtension(): CouponSendPlanExtension
    {
        // 通过反射创建实例，避免直接实例化
        $class = CouponSendPlanExtension::class;
        $reflection = new \ReflectionClass($class);

        return $reflection->newInstance();
    }

    private function createContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');

        return $container;
    }

    public function testLoadWithEmptyConfigLoadsSuccessfully(): void
    {
        $extension = $this->createExtension();
        $container = $this->createContainer();

        $extension->load([], $container);

        $this->assertFileExists(
            __DIR__ . '/../../src/Resources/config/services.yaml',
            'services.yaml 配置文件应该存在'
        );

        $this->assertNotEmpty(
            $container->getDefinitions(),
            'Container 不应为空，即使没有配置'
        );
    }

    public function testLoadMultipleTimes(): void
    {
        $extension = $this->createExtension();
        $container = $this->createContainer();

        // 多次加载应该不会出错
        $extension->load([], $container);
        $extension->load([], $container);

        $this->assertNotEmpty(
            $container->getDefinitions(),
            '多次加载后 Container 不应为空'
        );
    }
}
