<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\SiliconFlowBundle\Entity\ChatCompletionLog;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConversation;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowImageGeneration;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowModel;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowVideoGeneration;

final readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        $siliconFlow = $item->addChild('SiliconFlow 管理', ['icon' => 'fas fa-robot']);

        $siliconFlow->addChild('配置管理', ['icon' => 'fas fa-sliders-h'])
            ->setUri($this->linkGenerator->getCurdListPage(SiliconFlowConfig::class))
        ;

        $siliconFlow->addChild('模型缓存', ['icon' => 'fas fa-layer-group'])
            ->setUri($this->linkGenerator->getCurdListPage(SiliconFlowModel::class))
        ;

        $siliconFlow->addChild('对话记录', ['icon' => 'fas fa-comments'])
            ->setUri($this->linkGenerator->getCurdListPage(SiliconFlowConversation::class))
        ;

        $siliconFlow->addChild('聊天日志', ['icon' => 'fas fa-file-alt'])
            ->setUri($this->linkGenerator->getCurdListPage(ChatCompletionLog::class))
        ;

        $siliconFlow->addChild('图片生成记录', ['icon' => 'fas fa-image'])
            ->setUri($this->linkGenerator->getCurdListPage(SiliconFlowImageGeneration::class))
        ;

        $siliconFlow->addChild('视频生成记录', ['icon' => 'fas fa-film'])
            ->setUri($this->linkGenerator->getCurdListPage(SiliconFlowVideoGeneration::class))
        ;
    }
}

