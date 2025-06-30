<?php

namespace Tourze\CouponSendPlanBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;

class SendPlanTest extends TestCase
{
    private SendPlan $sendPlan;

    protected function setUp(): void
    {
        $this->sendPlan = new SendPlan();
    }

    public function testInitialState(): void
    {
        $this->assertNull($this->sendPlan->getId());
        $this->assertNull($this->sendPlan->getRemark());
        $this->assertNull($this->sendPlan->getSendTime());
        $this->assertFalse($this->sendPlan->isFinished());
        $this->assertNull($this->sendPlan->getCreatedFromIp());
        $this->assertNull($this->sendPlan->getUpdatedFromIp());
        $this->assertInstanceOf(ArrayCollection::class, $this->sendPlan->getCoupons());
        $this->assertInstanceOf(ArrayCollection::class, $this->sendPlan->getUsers());
        $this->assertCount(0, $this->sendPlan->getCoupons());
        $this->assertCount(0, $this->sendPlan->getUsers());
    }

    public function testSetAndGetRemark(): void
    {
        $remark = '测试备注';
        $this->sendPlan->setRemark($remark);
        $this->assertEquals($remark, $this->sendPlan->getRemark());
        
        $this->sendPlan->setRemark(null);
        $this->assertNull($this->sendPlan->getRemark());
    }

    public function testSetAndGetSendTime(): void
    {
        $sendTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $this->sendPlan->setSendTime($sendTime);
        $this->assertEquals($sendTime, $this->sendPlan->getSendTime());
        
        // 测试 DateTime 转换为 DateTimeImmutable
        $dateTime = new \DateTime('2024-01-02 15:00:00');
        $this->sendPlan->setSendTime($dateTime);
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->sendPlan->getSendTime());
        $this->assertEquals($dateTime->format('Y-m-d H:i:s'), $this->sendPlan->getSendTime()->format('Y-m-d H:i:s'));
    }

    public function testSetAndGetFinished(): void
    {
        $this->assertFalse($this->sendPlan->isFinished());
        
        $this->sendPlan->setFinished(true);
        $this->assertTrue($this->sendPlan->isFinished());
        
        $this->sendPlan->setFinished(false);
        $this->assertFalse($this->sendPlan->isFinished());
    }

    public function testAddAndRemoveCoupon(): void
    {
        $coupon1 = $this->createMock(Coupon::class);
        $coupon2 = $this->createMock(Coupon::class);
        
        // 添加优惠券
        $this->sendPlan->addCoupon($coupon1);
        $this->assertCount(1, $this->sendPlan->getCoupons());
        $this->assertTrue($this->sendPlan->getCoupons()->contains($coupon1));
        
        // 重复添加同一个优惠券不会增加数量
        $this->sendPlan->addCoupon($coupon1);
        $this->assertCount(1, $this->sendPlan->getCoupons());
        
        // 添加另一个优惠券
        $this->sendPlan->addCoupon($coupon2);
        $this->assertCount(2, $this->sendPlan->getCoupons());
        $this->assertTrue($this->sendPlan->getCoupons()->contains($coupon2));
        
        // 移除优惠券
        $this->sendPlan->removeCoupon($coupon1);
        $this->assertCount(1, $this->sendPlan->getCoupons());
        $this->assertFalse($this->sendPlan->getCoupons()->contains($coupon1));
        $this->assertTrue($this->sendPlan->getCoupons()->contains($coupon2));
    }

    public function testAddAndRemoveUser(): void
    {
        $user1 = $this->createMock(UserInterface::class);
        $user2 = $this->createMock(UserInterface::class);
        
        // 添加用户
        $this->sendPlan->addUser($user1);
        $this->assertCount(1, $this->sendPlan->getUsers());
        $this->assertTrue($this->sendPlan->getUsers()->contains($user1));
        
        // 重复添加同一个用户不会增加数量
        $this->sendPlan->addUser($user1);
        $this->assertCount(1, $this->sendPlan->getUsers());
        
        // 添加另一个用户
        $this->sendPlan->addUser($user2);
        $this->assertCount(2, $this->sendPlan->getUsers());
        $this->assertTrue($this->sendPlan->getUsers()->contains($user2));
        
        // 移除用户
        $this->sendPlan->removeUser($user1);
        $this->assertCount(1, $this->sendPlan->getUsers());
        $this->assertFalse($this->sendPlan->getUsers()->contains($user1));
        $this->assertTrue($this->sendPlan->getUsers()->contains($user2));
    }

    public function testSetAndGetCreatedFromIp(): void
    {
        $ip = '192.168.1.1';
        $this->sendPlan->setCreatedFromIp($ip);
        $this->assertEquals($ip, $this->sendPlan->getCreatedFromIp());
        
        $this->sendPlan->setCreatedFromIp(null);
        $this->assertNull($this->sendPlan->getCreatedFromIp());
    }

    public function testSetAndGetUpdatedFromIp(): void
    {
        $ip = '10.0.0.1';
        $this->sendPlan->setUpdatedFromIp($ip);
        $this->assertEquals($ip, $this->sendPlan->getUpdatedFromIp());
        
        $this->sendPlan->setUpdatedFromIp(null);
        $this->assertNull($this->sendPlan->getUpdatedFromIp());
    }

    public function testToStringWithoutId(): void
    {
        $this->assertEquals('#', (string) $this->sendPlan);
    }

    public function testToStringWithId(): void
    {
        // 使用反射设置私有属性 id
        $reflection = new \ReflectionClass($this->sendPlan);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->sendPlan, '12345');
        
        $this->assertEquals('#12345', (string) $this->sendPlan);
    }

    public function testFluentInterface(): void
    {
        $remark = '测试备注';
        $sendTime = new \DateTimeImmutable();
        $ip = '127.0.0.1';
        $coupon = $this->createMock(Coupon::class);
        $user = $this->createMock(UserInterface::class);
        
        $result = $this->sendPlan
            ->setRemark($remark)
            ->setSendTime($sendTime)
            ->setFinished(true)
            ->setCreatedFromIp($ip)
            ->setUpdatedFromIp($ip)
            ->addCoupon($coupon)
            ->addUser($user);
        
        $this->assertSame($this->sendPlan, $result);
    }
}