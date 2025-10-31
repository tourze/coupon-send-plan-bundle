<?php

declare(strict_types=1);

namespace Tourze\CouponSendPlanBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Tourze\CouponSendPlanBundle\Entity\SendPlan;

#[AdminCrud(routePath: '/coupon/send-plan', routeName: 'coupon_send_plan')]
final class SendPlanCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SendPlan::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('发送计划')
            ->setEntityLabelInPlural('发送计划')
            ->setSearchFields(['id', 'remark'])
            ->setDefaultSort(['sendTime' => 'DESC'])
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield DateTimeField::new('sendTime', '发送时间')
            ->setRequired(true)
            ->setHelp('计划发送优惠券的时间')
        ;

        yield AssociationField::new('coupons', '优惠券')
            ->setRequired(true)
            ->setHelp('选择要发送的优惠券')
            ->hideOnIndex()
        ;

        yield AssociationField::new('users', '接收用户')
            ->setRequired(true)
            ->setHelp('选择接收优惠券的用户')
            ->hideOnIndex()
        ;

        yield TextField::new('remark', '备注')
            ->setMaxLength(255)
            ->setHelp('可选的备注信息')
        ;

        yield BooleanField::new('finished', '已完成')
            ->setHelp('标记是否已完成发送')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->onlyOnDetail()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->onlyOnDetail()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('finished', '已完成'))
            ->add(DateTimeFilter::new('sendTime', '发送时间'))
        ;
    }
}
