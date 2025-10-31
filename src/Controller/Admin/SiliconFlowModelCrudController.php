<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowModel;

/**
 * @extends AbstractCrudController<SiliconFlowModel>
 */
#[AdminCrud(
    routePath: '/silicon-flow/model',
    routeName: 'silicon_flow_model'
)]
final class SiliconFlowModelCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SiliconFlowModel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('模型')
            ->setEntityLabelInPlural('模型列表')
            ->setPageTitle(Crud::PAGE_INDEX, '模型列表')
            ->setPageTitle(Crud::PAGE_NEW, '添加模型')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑模型')
            ->setPageTitle(Crud::PAGE_DETAIL, '模型详情')
            ->setDefaultSort(['createTime' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('modelId', '模型标识');
        yield TextField::new('objectType', '对象类型');
        yield BooleanField::new('isActive', '是否有效');
        yield ArrayField::new('metadata', '元数据')
            ->hideOnIndex()
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
            ->add(TextFilter::new('modelId', '模型标识'))
            ->add(BooleanFilter::new('isActive', '是否有效'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }
}

