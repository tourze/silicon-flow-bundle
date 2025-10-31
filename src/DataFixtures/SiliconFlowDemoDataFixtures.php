<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\DataFixtures;

use BizUserBundle\DataFixtures\BizUserFixtures;
use BizUserBundle\Entity\BizUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConversation;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowImageGeneration;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowVideoGeneration;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowConversationRepository;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowImageGenerationRepository;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowVideoGenerationRepository;

#[When(env: 'dev')]
#[When(env: 'test')]
class SiliconFlowDemoDataFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function __construct(
        private readonly SiliconFlowConversationRepository $conversationRepository,
        private readonly SiliconFlowImageGenerationRepository $imageGenerationRepository,
        private readonly SiliconFlowVideoGenerationRepository $videoGenerationRepository,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        /** @var BizUser $sender */
        $sender = $this->getReference(BizUserFixtures::ADMIN_USER_REFERENCE, BizUser::class);

        $this->createConversations($manager, $sender);
        $this->createImageGeneration($manager, $sender);
        $this->createVideoGeneration($manager, $sender);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['silicon_flow'];
    }

    public function getDependencies(): array
    {
        return [
            SiliconFlowConfigFixtures::class,
            BizUserFixtures::class,
        ];
    }

    private function createConversations(ObjectManager $manager, BizUser $sender): void
    {
        $entries = [
            [
                'question' => '帮我罗列 SiliconFlow 可提供的模型类型。',
                'answer' => "SiliconFlow 作为AI基础设施平台，通常提供多种类型的模型以满足不同场景需求。以下是其可能支持的模型类型分类：\n\n### **1. 基础大模型**\n- **通用大语言模型（LLM）**\n  - 类似GPT的多任务对话模型（中英文/多语言）\n  - 行业优化版大模型（金融、医疗、法律等垂直领域）\n- **多模态模型**\n  - 图文生成模型（如Stable Diffusion类）\n  - 视觉-语言联合模型（图像描述、视觉问答）\n\n### **2. 垂直领域专用模型**\n- **金融领域**\n  - 财报分析模型\n  - 风险预测模型\n- **医疗健康**\n  - 医学影像识别\n  - 临床文本结构化模型\n- **零售电商**\n  - 商品推荐模型\n  - 评论情感分析\n\n### **3. 生成式AI模型**\n- **文本生成**\n  - 营销文案生成\n  - 代码自动补全\n- **图像生成**\n  - 产品设计原型生成\n  - 风格化艺术创作\n- **音视频生成**\n  - 语音合成（TTS）\n  - 视频摘要生成\n\n### **4. 分析与决策模型**\n- **预测类模型**\n  - 时序预测（销量、股价等）\n  - 异常检测模型\n- **结构化数据处理**\n  - 表格数据预测\n  - 知识图谱构建\n\n### **5. 部署优化模型**\n- **轻量化模型**\n  - 移动端优化模型（TensorFlow Lite版）\n  - 边缘计算专用模型\n- **加速推理版本**\n  - 量化压缩模型（INT8/FP16）\n  - 蒸馏后的小参数量模型\n\n### **6. 其他特殊类型**\n- **隐私计算模型**\n  - 联邦学习模型\n  - 差分隐私训练版本\n- **开源模型托管**\n  - 第三方开源模型（如LLaMA、ChatGLM）的微调托管服务\n\n### **特点说明**\n- **灵活性**：支持用户上传自定义模型进行微调或部署\n- **场景适配**：提供从百亿参数大模型到轻量化SDK的全套解决方案\n- **持续更新**：模型库会随前沿研究（如MoE架构、Agent技术）动态扩展\n\n注：具体模型可用性需以SiliconFlow官方文档最新信息为准，部分模型可能需申请权限或企业版服务。",
                'model' => 'deepseek-ai/DeepSeek-V3',
                'create' => '2025-10-24 11:37:59',
                'update' => '2025-10-24 11:37:59',
            ],
            [
                'question' => '帮我罗列 SiliconFlow 可提供的模型类型。',
                'answer' => "SiliconFlow 作为AI加速和模型服务领域的平台，通常支持多种模型类型以覆盖不同场景需求。以下是 SiliconFlow 可能提供的模型类型分类（具体以官方文档为准）：\n\n---\n\n### **1. 基础模型（Foundation Models）**\n- **大语言模型（LLMs）**  \n  - 通用文本生成（如GPT类模型）\n  - 多语言支持模型\n  - 代码生成模型（如代码补全、解释）\n- **多模态模型**  \n  - 图文理解（CLIP风格）\n  - 文本生成图像/视频（Diffusion类模型）\n\n---\n\n### **2. 垂直领域模型**\n- **金融领域**  \n  - 财报分析、风险预测模型\n- **医疗健康**  \n  - 医学影像识别、病历文本解析\n- **法律合规**  \n  - 合同审核、法律问答\n- **零售电商**  \n  - 商品推荐、评论情感分析\n\n---\n\n### **3. 嵌入式/边缘端模型**\n- **轻量化模型**  \n  - MobileNet、TinyBERT等适配移动端的模型\n- **实时推理模型**  \n  - 低延迟的视觉检测（如YOLO变体）、语音识别\n\n---\n\n### **4. 开源模型托管**\n- **Hugging Face集成**  \n  - 支持Transformer系列（BERT、RoBERTa等）\n- **自定义模型上传**  \n  - 用户自训练的PyTorch/TensorFlow模型部署\n\n---\n\n### **5. 企业定制化模型**\n- **微调服务**  \n  - 基于客户数据的领域适配（如行业术语优化）\n- **私有化部署**  \n  - 支持敏感数据的本地化模型部署\n\n---\n\n### **6. 其他支持类型**\n- **传统机器学习模型**  \n  - Scikit-learn、XGBoost等经典模型\n- **向量模型**  \n  - 文本/图像嵌入（Embedding）用于检索\n\n---\n\n如需具体型号（如芯片适配型号或最新合作模型），建议直接查阅 SiliconFlow 官方模型库或联系其技术团队获取清单。",
                'model' => 'deepseek-ai/DeepSeek-V3',
                'create' => '2025-10-24 11:43:02',
                'update' => '2025-10-24 11:43:02',
            ],
        ];

        foreach ($entries as $entry) {
            $conversation = $this->conversationRepository->findOneBy([
                'question' => $entry['question'],
                'createTime' => new \DateTimeImmutable($entry['create']),
            ]);

            if (!$conversation instanceof SiliconFlowConversation) {
                $conversation = new SiliconFlowConversation();
                $conversation->setQuestion($entry['question']);
                $conversation->setCreateTime(new \DateTimeImmutable($entry['create']));
                $manager->persist($conversation);
            }

            $conversation->setConversationType(SiliconFlowConversation::TYPE_SINGLE);
            $conversation->setModel($entry['model']);
            $conversation->setAnswer($entry['answer']);
            $conversation->setSender($sender);
            $conversation->setContext(null);
            $conversation->setContextSnapshot(null);
            $conversation->setUpdateTime(new \DateTimeImmutable($entry['update']));
        }
    }

    private function createImageGeneration(ObjectManager $manager, BizUser $sender): void
    {
        $createdAt = new \DateTimeImmutable('2025-10-24 11:35:28');

        $imageGeneration = $this->imageGenerationRepository->findOneBy([
            'model' => 'Kwai-Kolors/Kolors',
            'prompt' => '一只穿着宇航服在月球上拍照的猫咪',
        ]);

        if (!$imageGeneration instanceof SiliconFlowImageGeneration) {
            $imageGeneration = new SiliconFlowImageGeneration();
            $imageGeneration->setModel('Kwai-Kolors/Kolors');
            $imageGeneration->setPrompt('一只穿着宇航服在月球上拍照的猫咪');
            $manager->persist($imageGeneration);
        }

        $imageGeneration->setNegativePrompt('模糊, 低质量');
        $imageGeneration->setImageSize('1024x1024');
        $imageGeneration->setBatchSize(1);
        $imageGeneration->setSeed(12345);
        $imageGeneration->setNumInferenceSteps(null);
        $imageGeneration->setRequestPayload($this->decodeJson('{"seed": 12345, "model": "Kwai-Kolors/Kolors", "prompt": "一只穿着宇航服在月球上拍照的猫咪", "batch_size": 1, "image_size": "1024x1024", "negative_prompt": "模糊, 低质量"}'));
        $imageGeneration->setResponsePayload($this->decodeJson('{"data": [{"url": "https://sc-maas.oss-cn-shanghai.aliyuncs.com/outputs%2F20251024%2Fnsosd14t3g.png?Expires=1761280528&OSSAccessKeyId=LTAI5tQnPSzwAnR8NmMzoQq4&Signature=Z7UaXMwX%2BYYT5Y2w1C%2F1CjcMPaI%3D"}], "seed": 12345, "images": [{"url": "https://sc-maas.oss-cn-shanghai.aliyuncs.com/outputs%2F20251024%2Fnsosd14t3g.png?Expires=1761280528&OSSAccessKeyId=LTAI5tQnPSzwAnR8NmMzoQq4&Signature=Z7UaXMwX%2BYYT5Y2w1C%2F1CjcMPaI%3D"}], "created": 1761276928, "timings": {"inference": 0.385}, "shared_id": "0"}'));
        $imageGeneration->setImageUrls('https://sc-maas.oss-cn-shanghai.aliyuncs.com/outputs%2F20251024%2Fnsosd14t3g.png?Expires=1761280528&OSSAccessKeyId=LTAI5tQnPSzwAnR8NmMzoQq4&Signature=Z7UaXMwX%2BYYT5Y2w1C%2F1CjcMPaI%3D');
        $imageGeneration->setInferenceTime(null);
        $imageGeneration->setResponseSeed(null);
        $imageGeneration->setSender($sender);
        $imageGeneration->setStatus('completed');
        $imageGeneration->setCreateTime($createdAt);
        $imageGeneration->setUpdateTime($createdAt);
    }

    private function createVideoGeneration(ObjectManager $manager, BizUser $sender): void
    {
        $createdAt = new \DateTimeImmutable('2025-10-24 14:10:00');
        $updatedAt = new \DateTimeImmutable('2025-10-24 14:15:43');

        $videoGeneration = $this->videoGenerationRepository->findOneBy(['requestId' => 'l8crqevq7lsf']);
        if (!$videoGeneration instanceof SiliconFlowVideoGeneration) {
            $videoGeneration = new SiliconFlowVideoGeneration();
            $videoGeneration->setRequestId('l8crqevq7lsf');
            $manager->persist($videoGeneration);
        }

        $videoGeneration->setModel('Wan-AI/Wan2.2-T2V-A14B');
        $videoGeneration->setPrompt('一只热气球在日出时升上高空');
        $videoGeneration->setNegativePrompt('模糊, 低质量');
        $videoGeneration->setImage(null);
        $videoGeneration->setImageSize('1280x720');
        $videoGeneration->setSeed(321);
        $videoGeneration->setRequestPayload($this->decodeJson('{"seed": 321, "model": "Wan-AI/Wan2.2-T2V-A14B", "prompt": "一只热气球在日出时升上高空", "image_size": "1280x720", "negative_prompt": "模糊, 低质量"}'));
        $videoGeneration->setSender($sender);
        $videoGeneration->setStatus('Succeed');
        $videoGeneration->setStatusReason('');
        $videoGeneration->setVideoUrls([
            'https://sc-maas.oss-cn-shanghai.aliyuncs.com/outputs%2F20251024%2F3tj07fdzwv.mp4?Expires=1761459214&OSSAccessKeyId=LTAI5tQnPSzwAnR8NmMzoQq4&Signature=F7RoDa1sPczYuHQYALTqUA1%2BYo4%3D',
        ]);
        $videoGeneration->setInferenceTime(214.083);
        $videoGeneration->setResultSeed(321);
        $videoGeneration->setResponsePayload($this->decodeJson('{"reason": "", "status": "Succeed", "results": {"seed": 321, "videos": [{"url": "https://sc-maas.oss-cn-shanghai.aliyuncs.com/outputs%2F20251024%2F3tj07fdzwv.mp4?Expires=1761459214&OSSAccessKeyId=LTAI5tQnPSzwAnR8NmMzoQq4&Signature=F7RoDa1sPczYuHQYALTqUA1%2BYo4%3D"}], "timings": {"inference": 214.083}}, "position": 0, "requestId": ""}'));
        $videoGeneration->setCreateTime($createdAt);
        $videoGeneration->setUpdateTime($updatedAt);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $json): array
    {
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($json, true);

        return $decoded;
    }
}
