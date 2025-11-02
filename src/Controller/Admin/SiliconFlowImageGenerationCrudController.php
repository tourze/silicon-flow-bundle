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
use Tourze\EasyAdminImagePreviewFieldBundle\Field\ImagePreviewField;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowImageGeneration;

/**
 * @extends AbstractCrudController<SiliconFlowImageGeneration>
 */
#[AdminCrud(
    routePath: '/silicon-flow/image-generation',
    routeName: 'silicon_flow_image_generation'
)]
final class SiliconFlowImageGenerationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SiliconFlowImageGeneration::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('图片生成记录')
            ->setEntityLabelInPlural('图片生成记录')
            ->setPageTitle(Crud::PAGE_INDEX, '图片生成记录')
            ->setPageTitle(Crud::PAGE_DETAIL, '生成详情')
            ->setDefaultSort(['createTime' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('model', '模型');
        yield AssociationField::new('sender', '发送者');
        yield TextareaField::new('prompt', '正向提示词')
            ->hideOnIndex()
            ->setFormTypeOption('attr', ['rows' => 5])
        ;
        yield TextareaField::new('negativePrompt', '反向提示词')
            ->hideOnIndex()
            ->setFormTypeOption('attr', ['rows' => 4])
        ;
        yield TextField::new('imageSize', '图像尺寸')
            ->hideOnForm()
        ;
        yield IntegerField::new('batchSize', '批量数量');
        yield IntegerField::new('numInferenceSteps', '采样步数')
            ->hideOnIndex()
        ;
        yield IntegerField::new('seed', '随机种子')
            ->hideOnIndex()
        ;
        yield NumberField::new('inferenceTime', '推理耗时(秒)')
            ->hideOnForm()
        ;
        yield IntegerField::new('responseSeed', '返回种子')
            ->hideOnIndex()
        ;
        yield TextField::new('status', '状态')
            ->hideOnForm()
        ;
        yield ImagePreviewField::new('imageUrls', '图片链接')
            ->hideOnForm()
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
