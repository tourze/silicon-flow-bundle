<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;

/**
 * @extends AbstractCrudController<SiliconFlowConfig>
 */
#[AdminCrud(
    routePath: '/silicon-flow/config',
    routeName: 'silicon_flow_config'
)]
final class SiliconFlowConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SiliconFlowConfig::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('配置')
            ->setEntityLabelInPlural('配置列表')
            ->setPageTitle(Crud::PAGE_INDEX, 'SiliconFlow 配置')
            ->setPageTitle(Crud::PAGE_NEW, '创建配置')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑配置')
            ->setPageTitle(Crud::PAGE_DETAIL, '配置详情')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', '配置名称');
        yield TextField::new('baseUrl', 'API 基础地址');
        yield TextareaField::new('apiToken', 'API Token')
            ->hideOnIndex()
            ->setFormTypeOption('attr', ['rows' => 4])
        ;
        yield BooleanField::new('isActive', '是否启用');
        yield IntegerField::new('priority', '优先级');
        yield TextareaField::new('description', '备注')
            ->hideOnIndex()
            ->setFormTypeOption('attr', ['rows' => 4])
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
        ;
        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '配置名称'))
            ->add(BooleanFilter::new('isActive', '是否启用'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }
}
