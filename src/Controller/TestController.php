<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Controller;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Repository\BizUserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Tourze\SiliconFlowBundle\Client\SiliconFlowApiClient;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConversation;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowImageGeneration;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowModel;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowVideoGeneration;
use Tourze\SiliconFlowBundle\Exception\ApiException;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowConfigRepository;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowConversationRepository;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowImageGenerationRepository;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowModelRepository;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowVideoGenerationRepository;
use Tourze\SiliconFlowBundle\Request\CreateChatMessagesRequest;
use Tourze\SiliconFlowBundle\Request\CreateImageGenerationRequest;
use Tourze\SiliconFlowBundle\Request\GetModelsRequest;
use Tourze\SiliconFlowBundle\Request\GetVideoStatusRequest;
use Tourze\SiliconFlowBundle\Request\SubmitVideoRequest;
use Tourze\SiliconFlowBundle\Service\ChatCompletionService;
use Tourze\SiliconFlowBundle\Service\ImageProcessingService;
use Tourze\SiliconFlowBundle\Service\ResponseExtractor;

#[Route(path: '/silicon/test', name: 'silicon_flow_test_')]
final class TestController extends AbstractController
{
    public function __construct(
        private readonly ChatCompletionService $chatCompletionService,
        private readonly SiliconFlowApiClient $apiClient,
        private readonly SiliconFlowConfigRepository $configRepository,
        private readonly BizUserRepository $bizUserRepository,
        private readonly SiliconFlowConversationRepository $conversationRepository,
        private readonly SiliconFlowImageGenerationRepository $imageGenerationRepository,
        private readonly SiliconFlowVideoGenerationRepository $videoGenerationRepository,
        private readonly SiliconFlowModelRepository $modelRepository,
        private readonly ResponseExtractor $responseExtractor,
        private readonly ImageProcessingService $imageProcessingService,
    ) {
    }

    #[Route(path: '/chat-completion', name: 'chat_completion', methods: ['POST', 'GET'])]
    public function chatCompletion(): JsonResponse
    {
        $this->requireActiveConfig();
        $this->requireDefaultUser();

        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => '简要介绍 SiliconFlow 的产品能力。'],
        ];

        $log = $this->chatCompletionService->createCompletion('deepseek-ai/DeepSeek-V3', $messages, [
            'temperature' => 0.4,
        ]);

        return new JsonResponse([
            'status' => 'ok',
            'data' => $log->getResponsePayload(),
        ]);
    }

    #[Route(path: '/chat-messages', name: 'chat_messages', methods: ['POST', 'GET'])]
    public function chatMessages(): JsonResponse
    {
        $config = $this->requireActiveConfig();
        $sender = $this->requireDefaultUser();

        $request = new CreateChatMessagesRequest(
            $config,
            'deepseek-ai/DeepSeek-V3',
            [
                ['role' => 'system', 'content' => '你是一个专业的产品经理助手。'],
                ['role' => 'user', 'content' => '帮我罗列 SiliconFlow 可提供的模型类型。'],
            ],
            1024,
            [
                'temperature' => 0.6,
            ],
            timeout: 120,
        );

        $response = $this->apiClient->request($request);

        $conversation = new SiliconFlowConversation();
        $conversation->setConversationType(SiliconFlowConversation::TYPE_SINGLE);
        /** @var string $modelValue */
        $modelValue = $response['model'] ?? $request->getModel();
        $conversation->setModel($modelValue);
        $conversation->setQuestion('帮我罗列 SiliconFlow 可提供的模型类型。');
        $conversation->setAnswer($this->responseExtractor->extractAnswerFromMessagesResponse($response));
        $conversation->setSender($sender);

        $this->conversationRepository->save($conversation);

        return new JsonResponse([
            'status' => 'ok',
            'data' => $this->imageProcessingService->replaceResponseImageUrls($response),
        ]);
    }

    #[Route(path: '/image-generation', name: 'image_generation', methods: ['POST', 'GET'])]
    public function imageGeneration(): JsonResponse
    {
        $config = $this->requireActiveConfig();
        $sender = $this->requireDefaultUser();

        $request = new CreateImageGenerationRequest(
            $config,
            'Kwai-Kolors/Kolors',
            '一只绑着蝴蝶结的猫咪在沙发上睡觉',
            negativePrompt: '模糊, 低质量',
            imageSize: '1024x1024',
            batchSize: 1,
            seed: 12345,
            timeout: 120,
        );

        $response = $this->apiClient->request($request);
        $response = $this->imageProcessingService->replaceResponseImageUrls($response);

        $imageGeneration = new SiliconFlowImageGeneration();
        $imageGeneration->setModel($request->getModel());
        $imageGeneration->setPrompt($request->getPrompt());
        $imageGeneration->setNegativePrompt($request->getNegativePrompt());
        $imageGeneration->setImageSize($request->getImageSize());
        $imageGeneration->setBatchSize($request->getBatchSize() ?? 1);
        $imageGeneration->setSeed($request->getSeed());
        $imageGeneration->setNumInferenceSteps($request->getNumInferenceSteps());
        $imageGeneration->setRequestPayload($request->toArray());
        $imageGeneration->setResponsePayload($response);
        $imageUrls = $this->responseExtractor->extractImageUrls($response);
        $imageGeneration->setImageUrls($this->responseExtractor->stringifyUrls($imageUrls));
        $imageGeneration->setSender($sender);
        $imageGeneration->setStatus('completed');

        $this->imageGenerationRepository->save($imageGeneration);

        return new JsonResponse([
            'status' => 'ok',
            'data' => $response,
        ]);
    }

    #[Route(path: '/video-submit', name: 'video_submit', methods: ['POST', 'GET'])]
    public function submitVideo(): JsonResponse
    {
        $config = $this->requireActiveConfig();
        $sender = $this->requireDefaultUser();

        $payload = [
            'model' => 'Wan-AI/Wan2.2-T2V-A14B',
            'prompt' => '一只热气球在日出时升上高空',
            'image_size' => '1280x720',
            'negative_prompt' => '模糊, 低质量',
            'seed' => 321,
        ];

        $request = new SubmitVideoRequest($config, $payload, timeout: 120);
        $response = $this->apiClient->request($request);

        $videoGeneration = new SiliconFlowVideoGeneration();
        $videoGeneration->setModel($payload['model']);
        $videoGeneration->setPrompt($payload['prompt']);
        $videoGeneration->setNegativePrompt($payload['negative_prompt']);
        $videoGeneration->setImageSize($payload['image_size']);
        $videoGeneration->setSeed($payload['seed']);
        $requestId = isset($response['requestId']) ? $response['requestId'] : null;
        if (null !== $requestId) {
            /** @var string $requestIdValue */
            $requestIdValue = $requestId;
            $videoGeneration->setRequestId($requestIdValue);
        } else {
            $videoGeneration->setRequestId(null);
        }
        $videoGeneration->setRequestPayload($payload);
        $videoGeneration->setSender($sender);
        $videoGeneration->setStatus('submitted');

        $this->videoGenerationRepository->save($videoGeneration);

        return new JsonResponse([
            'status' => 'ok',
            'data' => $response,
        ]);
    }

    #[Route(path: '/video-status', name: 'video_status', methods: ['GET'])]
    public function getVideoStatus(Request $request): JsonResponse
    {
        $config = $this->requireActiveConfig();

        $requestId = $request->query->get('requestId');

        if (null === $requestId) {
            $latest = $this->videoGenerationRepository->findOneBy([], ['id' => 'DESC']);
            if (null === $latest || null === $latest->getRequestId()) {
                throw new ApiException('请先提交视频生成任务或提供 requestId 参数。');
            }
            $requestId = $latest->getRequestId();
            $videoGeneration = $latest;
        } else {
            $videoGeneration = $this->videoGenerationRepository->findOneBy(['requestId' => $requestId]);
            if (null === $videoGeneration) {
                throw new ApiException(sprintf('未找到 requestId 为 %s 的视频生成任务。', $requestId));
            }
        }

        $statusRequest = new GetVideoStatusRequest($config, (string) $requestId);
        $response = $this->apiClient->request($statusRequest);

        $videoGeneration->setResponsePayload($response);
        $videoGeneration->setStatus($this->responseExtractor->extractStatus($response, $videoGeneration->getStatus()));
        $videoGeneration->setStatusReason($this->responseExtractor->extractStatusReason($response));
        $videoGeneration->setInferenceTime($this->responseExtractor->extractInferenceTime($response));
        $videoGeneration->setResultSeed($this->responseExtractor->extractResultSeed($response));
        $videoGeneration->setVideoUrls($this->responseExtractor->extractVideoUrls($response));

        $this->videoGenerationRepository->save($videoGeneration);

        return new JsonResponse([
            'status' => 'ok',
            'data' => $response,
        ]);
    }

    #[Route(path: '/models', name: 'models', methods: ['GET'])]
    public function getModels(): JsonResponse
    {
        $config = $this->requireActiveConfig();

        $request = new GetModelsRequest($config);
        $response = $this->apiClient->request($request);

        $models = $response['data'] ?? [];
        /** @var array<mixed> $models */
        $this->syncModels($models);

        return new JsonResponse([
            'status' => 'ok',
            'data' => $response,
        ]);
    }

    /**
     * 同步模型列表到数据库
     *
     * @param array<mixed> $models
     */
    private function syncModels(array $models): void
    {
        $ids = $this->extractModelIds($models);
        $this->modelRepository->deactivateAllExcept($ids);

        foreach ($models as $item) {
            if (!is_array($item)) {
                continue;
            }
            /** @var array<string, mixed> $itemData */
            $itemData = $item;
            $this->processModelItem($itemData);
        }

        $this->modelRepository->flush();
    }

    /**
     * 从模型数据中提取ID列表
     *
     * @param array<mixed> $models
     *
     * @return array<string>
     */
    private function extractModelIds(array $models): array
    {
        return array_map(
            static fn (mixed $item): string => is_array($item) && isset($item['id']) && is_string($item['id']) ? $item['id'] : '',
            $models
        );
    }

    /**
     * 处理单个模型项
     *
     * @param array<string, mixed> $item
     */
    private function processModelItem(array $item): void
    {
        $modelId = isset($item['id']) && is_string($item['id']) ? $item['id'] : '';
        if ('' === $modelId) {
            return;
        }

        $model = $this->modelRepository->findOneBy(['modelId' => $modelId]);
        if (null === $model) {
            $model = new SiliconFlowModel();
            $model->setModelId($modelId);
        }

        $objectValue = $item['object'] ?? 'model';
        $model->setObjectType(is_string($objectValue) ? $objectValue : 'model');
        $model->setMetadata($item);
        $model->setIsActive(true);

        $this->modelRepository->save($model, false);
    }

    private function requireActiveConfig(): \Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig
    {
        $config = $this->configRepository->findActiveConfig();
        if (null === $config) {
            throw new ApiException('未找到激活的 SiliconFlow 配置信息。');
        }

        return $config;
    }

    private function requireDefaultUser(): BizUser
    {
        $user = $this->bizUserRepository->findOneBy(['username' => 'admin']);
        if (null === $user) {
            throw new ApiException('未找到用户名为 admin 的演示用户，请先导入测试数据。');
        }

        if (false === $user->isValid()) {
            throw new ApiException('演示用户 admin 已被禁用，无法执行示例操作。');
        }

        return $user;
    }
}
