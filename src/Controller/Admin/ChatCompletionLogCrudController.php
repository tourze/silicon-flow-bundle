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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\SiliconFlowBundle\Entity\ChatCompletionLog;

/**
 * @extends AbstractCrudController<ChatCompletionLog>
 */
#[AdminCrud(
    routePath: '/silicon-flow/chat-completion-log',
    routeName: 'silicon_flow_chat_completion_log'
)]
final class ChatCompletionLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ChatCompletionLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('聊天日志')
            ->setEntityLabelInPlural('聊天日志')
            ->setPageTitle(Crud::PAGE_INDEX, '聊天日志')
            ->setPageTitle(Crud::PAGE_DETAIL, '日志详情')
            ->setDefaultSort(['createTime' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('requestId', '请求 ID')
            ->hideOnForm()
        ;
        yield TextField::new('model', '模型');
        yield TextField::new('status', '状态');
        yield IntegerField::new('promptTokens', 'Prompt Tokens')
            ->hideOnForm()
        ;
        yield IntegerField::new('completionTokens', 'Completion Tokens')
            ->hideOnForm()
        ;
        yield IntegerField::new('totalTokens', '总 Tokens')
            ->hideOnForm()
        ;
        yield TextareaField::new('errorMessage', '错误信息')
            ->onlyOnDetail()
            ->setFormTypeOption('attr', ['rows' => 4])
        ;
        yield ArrayField::new('requestPayload', '请求载荷')
            ->onlyOnDetail()
        ;
        yield ArrayField::new('responsePayload', '响应载荷')
            ->onlyOnDetail()
        ;
        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('requestId', '请求 ID'))
            ->add(TextFilter::new('model', '模型'))
            ->add(TextFilter::new('status', '状态'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }
}
