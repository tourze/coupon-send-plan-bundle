<?php

namespace Tourze\CouponSendPlanBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\CouponSendPlanBundle\DependencyInjection\CouponSendPlanExtension;

class CouponSendPlanExtensionTest extends TestCase
{
    private CouponSendPlanExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new CouponSendPlanExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoadServicesConfiguration(): void
    {
        // 执行加载
        $this->extension->load([], $this->container);

        // 验证服务配置被正确加载（使用资源扫描方式）
        $definitions = $this->container->getDefinitions();
        
        // 检查是否有定义被加载
        $this->assertNotEmpty($definitions);
        
        // 验证自动配置和自动装配的默认设置
        $hasAutowire = false;
        $hasAutoconfigure = false;
        
        foreach ($definitions as $definition) {
            if ($definition->isAutowired()) {
                $hasAutowire = true;
            }
            if ($definition->isAutoconfigured()) {
                $hasAutoconfigure = true;
            }
        }
        
        $this->assertTrue($hasAutowire || $hasAutoconfigure);
    }

    public function testLoadWithEmptyConfiguration(): void
    {
        // 使用空配置加载
        $this->extension->load([], $this->container);

        // 验证容器不为空
        $this->assertNotEmpty($this->container->getDefinitions());
    }

    public function testLoadMultipleTimes(): void
    {
        // 多次加载不应该出错
        $this->extension->load([], $this->container);
        $this->extension->load([], $this->container);

        // 验证服务存在
        $this->assertTrue($this->container->hasDefinition('Tourze\CouponSendPlanBundle\Service\PlanService'));
    }
}