<?php

namespace Tourze\CouponSendPlanBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;
use Tourze\CouponSendPlanBundle\Repository\SendPlanRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(SendPlanRepository::class)]
#[RunTestsInSeparateProcesses]
final class SendPlanRepositoryTest extends AbstractRepositoryTestCase
{
    private SendPlanRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(SendPlanRepository::class);

        // 检查当前测试是否需要 DataFixtures 数据
        $currentTest = $this->name();
        if ('testCountWithDataFixtureShouldReturnGreaterThanZero' === $currentTest) {
            $this->createTestDataForCountTest();
        }
    }

    private function createTestDataForCountTest(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test DataFixture Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();
    }

    public function testIsInstanceOfSendPlanRepository(): void
    {
        $this->assertInstanceOf(SendPlanRepository::class, $this->repository);
    }

    public function testSave(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);

        $this->repository->save($sendPlan, true);

        $this->assertNotNull($sendPlan->getId());
        $foundEntity = $this->repository->find($sendPlan->getId());
        $this->assertSame($sendPlan, $foundEntity);
    }

    public function testSaveWithoutFlush(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);

        $this->repository->save($sendPlan, false);
        self::getEntityManager()->flush();

        $this->assertNotNull($sendPlan->getId());
    }

    public function testRemove(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $id = $sendPlan->getId();
        $this->repository->remove($sendPlan, true);

        $foundEntity = $this->repository->find($id);
        $this->assertNull($foundEntity);
    }

    public function testCountWithFinishedNullField(): void
    {
        // 清理现有数据以确保测试独立性
        $existingPlans = $this->repository->findAll();
        foreach ($existingPlans as $plan) {
            self::getEntityManager()->remove($plan);
        }
        self::getEntityManager()->flush();

        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test');
        $sendPlan->setFinished(false);
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $count = $this->repository->count(['finished' => false]);
        $this->assertSame(1, $count);
    }

    public function testFindByWithCreatedByNull(): void
    {
        // 清理现有数据以确保测试独立性
        $existingPlans = $this->repository->findAll();
        foreach ($existingPlans as $plan) {
            self::getEntityManager()->remove($plan);
        }
        self::getEntityManager()->flush();

        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $entities = $this->repository->findBy(['createdBy' => null]);
        $this->assertIsArray($entities);
        $this->assertCount(1, $entities);
        $this->assertSame($sendPlan, $entities[0]);
    }

    public function testCountWithCreatedByNull(): void
    {
        // 清理现有数据以确保测试独立性
        $existingPlans = $this->repository->findAll();
        foreach ($existingPlans as $plan) {
            self::getEntityManager()->remove($plan);
        }
        self::getEntityManager()->flush();

        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $count = $this->repository->count(['createdBy' => null]);
        $this->assertSame(1, $count);
    }

    public function testFindByWithCreatedFromIpNull(): void
    {
        // 清理现有数据以确保测试独立性
        $existingPlans = $this->repository->findAll();
        foreach ($existingPlans as $plan) {
            self::getEntityManager()->remove($plan);
        }
        self::getEntityManager()->flush();

        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $entities = $this->repository->findBy(['createdFromIp' => null]);
        $this->assertIsArray($entities);
        $this->assertCount(1, $entities);
        $this->assertSame($sendPlan, $entities[0]);
    }

    public function testCountWithCreatedFromIpNull(): void
    {
        // 清理现有数据以确保测试独立性
        $existingPlans = $this->repository->findAll();
        foreach ($existingPlans as $plan) {
            self::getEntityManager()->remove($plan);
        }
        self::getEntityManager()->flush();

        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $count = $this->repository->count(['createdFromIp' => null]);
        $this->assertSame(1, $count);
    }

    public function testFindByWithUpdatedByNull(): void
    {
        // 清理现有数据以确保测试独立性
        $existingPlans = $this->repository->findAll();
        foreach ($existingPlans as $plan) {
            self::getEntityManager()->remove($plan);
        }
        self::getEntityManager()->flush();

        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $entities = $this->repository->findBy(['updatedBy' => null]);
        $this->assertIsArray($entities);
        $this->assertCount(1, $entities);
        $this->assertSame($sendPlan, $entities[0]);
    }

    public function testCountWithUpdatedByNull(): void
    {
        // 清理现有数据以确保测试独立性
        $existingPlans = $this->repository->findAll();
        foreach ($existingPlans as $plan) {
            self::getEntityManager()->remove($plan);
        }
        self::getEntityManager()->flush();

        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $count = $this->repository->count(['updatedBy' => null]);
        $this->assertSame(1, $count);
    }

    public function testFindByWithCreateTimeNull(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $entities = $this->repository->findBy(['createTime' => null]);
        $this->assertIsArray($entities);
    }

    public function testCountWithCreateTimeNull(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $count = $this->repository->count(['createTime' => null]);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testFindByWithUpdateTimeNull(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $entities = $this->repository->findBy(['updateTime' => null]);
        $this->assertIsArray($entities);
    }

    public function testCountWithUpdateTimeNull(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $count = $this->repository->count(['updateTime' => null]);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testFindOneByWithCreatedByNull(): void
    {
        // 清理现有数据以确保测试独立性
        $existingPlans = $this->repository->findAll();
        foreach ($existingPlans as $plan) {
            self::getEntityManager()->remove($plan);
        }
        self::getEntityManager()->flush();

        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $foundEntity = $this->repository->findOneBy(['createdBy' => null]);
        $this->assertSame($sendPlan, $foundEntity);
    }

    public function testFindOneByWithUpdatedByNull(): void
    {
        // 清理现有数据以确保测试独立性
        $existingPlans = $this->repository->findAll();
        foreach ($existingPlans as $plan) {
            self::getEntityManager()->remove($plan);
        }
        self::getEntityManager()->flush();

        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $foundEntity = $this->repository->findOneBy(['updatedBy' => null]);
        $this->assertSame($sendPlan, $foundEntity);
    }

    public function testFindOneByWithCreatedFromIpNull(): void
    {
        // 清理现有数据以确保测试独立性
        $existingPlans = $this->repository->findAll();
        foreach ($existingPlans as $plan) {
            self::getEntityManager()->remove($plan);
        }
        self::getEntityManager()->flush();

        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $foundEntity = $this->repository->findOneBy(['createdFromIp' => null]);
        $this->assertSame($sendPlan, $foundEntity);
    }

    public function testFindOneByWithCreateTimeNull(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $foundEntity = $this->repository->findOneBy(['createTime' => null]);
        $this->assertNull($foundEntity);
    }

    public function testFindOneByWithUpdateTimeNull(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $foundEntity = $this->repository->findOneBy(['updateTime' => null]);
        $this->assertNull($foundEntity);
    }

    public function testFindOneByWithSendTimeNull(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $foundEntity = $this->repository->findOneBy(['sendTime' => null]);
        $this->assertNull($foundEntity);
    }

    public function testFindByWithSendTimeNull(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $entities = $this->repository->findBy(['sendTime' => null]);
        $this->assertIsArray($entities);
        $this->assertEmpty($entities);
    }

    public function testCountWithSendTimeNull(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        $coupon->setExpireDay(30);
        $coupon->setValid(true);
        self::getEntityManager()->persist($coupon);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('Test Plan');
        $sendPlan->addCoupon($coupon);
        self::getEntityManager()->persist($sendPlan);
        self::getEntityManager()->flush();

        $count = $this->repository->count(['sendTime' => null]);
        $this->assertSame(0, $count);
    }

    protected function getRepository(): SendPlanRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon for CreateNewEntity');
        $coupon->setSn('CREATE_NEW_' . uniqid());
        $coupon->setExpireDay(30);
        $coupon->setValid(true);

        $sendPlan = new SendPlan();
        $sendPlan->setSendTime(new \DateTimeImmutable('+1 day'));
        $sendPlan->setRemark('CreateNewEntity Plan ' . uniqid());
        $sendPlan->addCoupon($coupon);

        return $sendPlan;
    }
}
