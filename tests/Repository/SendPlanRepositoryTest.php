<?php

namespace Tourze\CouponSendPlanBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;
use Tourze\CouponSendPlanBundle\Repository\SendPlanRepository;

class SendPlanRepositoryTest extends TestCase
{
    private SendPlanRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new SendPlanRepository($this->registry);
    }

    public function testConstructor(): void
    {
        // 验证仓库成功创建
        $this->assertInstanceOf(SendPlanRepository::class, $this->repository);
    }

    public function testExtendsServiceEntityRepository(): void
    {
        // 验证继承关系
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $this->repository);
    }

    public function testRepositoryMethods(): void
    {
        // 验证 repository 是 ServiceEntityRepository 的实例
        // 这确保了它拥有所有标准的 repository 方法
        $methods = get_class_methods($this->repository);
        
        // 验证标准方法存在
        $this->assertContains('find', $methods);
        $this->assertContains('findOneBy', $methods);
        $this->assertContains('findAll', $methods);
        $this->assertContains('findBy', $methods);
    }
    
    public function testRepositoryInstantiation(): void
    {
        // 验证仓库可以被正确实例化
        $repository = new SendPlanRepository($this->registry);
        $this->assertNotNull($repository);
    }
}