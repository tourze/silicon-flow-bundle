<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConversation;

/**
 * @extends AbstractCrudController<SiliconFlowConversation>
 */
#[AdminCrud(
    routePath: '/silicon-flow/conversation',
    routeName: 'silicon_flow_conversation'
)]
final class SiliconFlowConversationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SiliconFlowConversation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('对话')
            ->setEntityLabelInPlural('对话记录')
            ->setPageTitle(Crud::PAGE_INDEX, '对话记录')
            ->setPageTitle(Crud::PAGE_DETAIL, '对话详情')
            ->setDefaultSort(['createTime' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield ChoiceField::new('conversationType', '对话类型')
            ->setChoices([
                '单轮对话' => SiliconFlowConversation::TYPE_SINGLE,
                '连续对话' => SiliconFlowConversation::TYPE_CONTINUOUS,
            ])
        ;
        yield AssociationField::new('sender', '发送者');
        yield AssociationField::new('context', '上条对话')
            ->hideOnIndex()
        ;
        yield TextareaField::new('question', '问题')
            ->setFormTypeOption('attr', ['rows' => 4])
        ;
        yield TextareaField::new('answer', '回答')
            ->hideOnIndex()
            ->setFormTypeOption('attr', ['rows' => 6])
        ;
        yield TextareaField::new('contextSnapshot', '上下文快照')
            ->onlyOnDetail()
            ->setFormTypeOption('attr', ['rows' => 8])
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
            ->add(ChoiceFilter::new('conversationType', '对话类型')->setChoices([
                '单轮对话' => SiliconFlowConversation::TYPE_SINGLE,
                '连续对话' => SiliconFlowConversation::TYPE_CONTINUOUS,
            ]))
            ->add(EntityFilter::new('sender', '发送者'))
            ->add(EntityFilter::new('context', '上条对话'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }
}

