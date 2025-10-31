<?php

namespace Tourze\CouponSendPlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponSendPlanBundle\Repository\SendPlanRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: SendPlanRepository::class)]
#[ORM\Table(name: 'coupon_send_plan', options: ['comment' => '发送计划'])]
class SendPlan implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;
    use CreatedFromIpAware;

    /**
     * @var Collection<int, Coupon>
     */
    #[ORM\ManyToMany(targetEntity: Coupon::class, fetch: 'EXTRA_LAZY', cascade: ['persist'])]
    private Collection $coupons;

    /**
     * @var Collection<int, UserInterface>
     */
    #[ORM\ManyToMany(targetEntity: UserInterface::class, fetch: 'EXTRA_LAZY', cascade: ['persist'])]
    private Collection $users;

    #[Assert\NotNull(message: 'Send time is required')]
    #[IndexColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '发送时间'])]
    private ?\DateTimeImmutable $sendTime = null;

    #[Assert\Length(max: 255, maxMessage: 'Remark cannot be longer than {{ limit }} characters')]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[Assert\Type(type: 'bool', message: 'Finished must be a boolean value')]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '已完成'])]
    private bool $finished = false;

    public function __construct()
    {
        $this->coupons = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '#';
        }

        return "#{$this->getId()}";
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    /**
     * @return Collection<int, Coupon>
     */
    public function getCoupons(): Collection
    {
        return $this->coupons;
    }

    public function addCoupon(Coupon $coupon): void
    {
        if (!$this->coupons->contains($coupon)) {
            $this->coupons->add($coupon);
        }
    }

    public function removeCoupon(Coupon $coupon): void
    {
        $this->coupons->removeElement($coupon);
    }

    /**
     * @return Collection<int, UserInterface>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(UserInterface $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    public function removeUser(UserInterface $user): void
    {
        $this->users->removeElement($user);
    }

    public function getSendTime(): ?\DateTimeImmutable
    {
        return $this->sendTime;
    }

    public function setSendTime(\DateTimeInterface $sendTime): void
    {
        $this->sendTime = $sendTime instanceof \DateTimeImmutable ? $sendTime : \DateTimeImmutable::createFromInterface($sendTime);
    }

    public function isFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): void
    {
        $this->finished = $finished;
    }
}
