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
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowVideoGeneration;

/**
 * @extends AbstractCrudController<SiliconFlowVideoGeneration>
 */
#[AdminCrud(
    routePath: '/silicon-flow/video-generation',
    routeName: 'silicon_flow_video_generation'
)]
final class SiliconFlowVideoGenerationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SiliconFlowVideoGeneration::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('视频生成记录')
            ->setEntityLabelInPlural('视频生成记录')
            ->setPageTitle(Crud::PAGE_INDEX, '视频生成记录')
            ->setPageTitle(Crud::PAGE_DETAIL, '生成详情')
            ->setDefaultSort(['createTime' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('requestId', '请求 ID')
            ->hideOnForm()
        ;
        yield TextField::new('model', '模型');
        yield AssociationField::new('sender', '发送者');
        yield TextareaField::new('prompt', '提示词')
            ->hideOnIndex()
            ->setFormTypeOption('attr', ['rows' => 5])
        ;
        yield TextareaField::new('negativePrompt', '反向提示词')
            ->hideOnIndex()
            ->setFormTypeOption('attr', ['rows' => 4])
        ;
        yield TextareaField::new('image', '参考图像')
            ->hideOnIndex()
            ->setFormTypeOption('attr', ['rows' => 4])
        ;
        yield TextField::new('imageSize', '图像尺寸')
            ->hideOnForm()
        ;
        yield IntegerField::new('seed', '随机种子')
            ->hideOnIndex()
        ;
        yield TextField::new('status', '状态')
            ->hideOnForm()
        ;
        yield TextareaField::new('statusReason', '状态说明')
            ->onlyOnDetail()
            ->setFormTypeOption('attr', ['rows' => 4])
        ;
        yield NumberField::new('inferenceTime', '推理耗时(秒)')
            ->hideOnForm()
        ;
        yield IntegerField::new('resultSeed', '返回种子')
            ->hideOnIndex()
        ;
        yield ArrayField::new('videoUrls', '视频链接')
            ->onlyOnDetail()
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
        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('requestId', '请求 ID'))
            ->add(TextFilter::new('model', '模型'))
            ->add(EntityFilter::new('sender', '发送者'))
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
