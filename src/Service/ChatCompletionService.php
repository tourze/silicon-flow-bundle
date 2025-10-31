<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Tourze\SiliconFlowBundle\Client\SiliconFlowApiClient;
use Tourze\SiliconFlowBundle\Entity\ChatCompletionLog;
use Tourze\SiliconFlowBundle\Exception\ApiException;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowConfigRepository;
use Tourze\SiliconFlowBundle\Request\CreateChatCompletionRequest;

/**
 * SiliconFlow 聊天完成服务
 *
 * 提供完整的聊天完成业务流程，包括：
 * - 配置管理和自动选择
 * - API 请求发送和响应处理
 * - 完整的日志记录和错误追踪
 * - Token 使用量统计
 * - 异常处理和重试机制
 */
final class ChatCompletionService
{
    public function __construct(
        private readonly SiliconFlowApiClient $apiClient,
        private readonly SiliconFlowConfigRepository $configRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * 创建聊天完成请求
     *
     * @param string $model 模型名称，如 'deepseek-chat'
     * @param array<int, array<string, mixed>> $messages 消息列表，遵循 OpenAI 格式
     * @param array<string, mixed> $options 额外请求选项，如 temperature、max_tokens 等
     * @param int|null $timeout 超时时间（秒），为空则使用默认值
     * @return ChatCompletionLog 包含请求和响应数据的日志记录
     * @throws ApiException 当配置缺失或 API 请求失败时
     */
    public function createCompletion(string $model, array $messages, array $options = [], ?int $timeout = null): ChatCompletionLog
    {
        $config = $this->configRepository->findActiveConfig();

        if (null === $config) {
            throw new ApiException('未找到可用的 SiliconFlow 配置，无法发起请求。');
        }

        $request = new CreateChatCompletionRequest(
            $config,
            $model,
            $messages,
            $options,
            $timeout,
        );

        $payload = $request->toArray();

        $log = new ChatCompletionLog();
        $log->setModel($request->getModel());
        $log->setRequestPayload($payload);

        try {
            $response = $this->apiClient->request($request);
            $this->applySuccessResponse($log, $response);
        } catch (ApiException $exception) {
            $this->applyFailure($log, $exception->getMessage());
            $this->persist($log);

            $this->logger->error('SiliconFlow Chat Completion 请求失败', [
                'error' => $exception->getMessage(),
                'model' => $request->getModel(),
            ]);

            throw $exception;
        } catch (\Throwable $exception) {
            $this->applyFailure($log, $exception->getMessage());
            $this->persist($log);

            $this->logger->error('SiliconFlow Chat Completion 出现未捕获异常', [
                'error' => $exception->getMessage(),
                'model' => $request->getModel(),
            ]);

            throw new ApiException('SiliconFlow Chat Completion 请求失败：' . $exception->getMessage(), 0, $exception);
        }

        $this->persist($log);

        return $log;
    }

    /**
     * @param array<string, mixed> $response
     */
    private function applySuccessResponse(ChatCompletionLog $log, array $response): void
    {
        $log->setStatus('success');
        $log->setErrorMessage(null);

        if (isset($response['id']) && is_string($response['id'])) {
            $log->setRequestId($response['id']);
        }

        if (isset($response['model']) && is_string($response['model']) && '' !== $response['model']) {
            $log->setModel($response['model']);
        }

        $log->setResponsePayload($response);

        $usage = $response['usage'] ?? [];
        if (!is_array($usage)) {
            $usage = [];
        }

        $promptTokens = isset($usage['prompt_tokens']) && (is_int($usage['prompt_tokens']) || is_string($usage['prompt_tokens']))
            ? (int) $usage['prompt_tokens']
            : 0;

        $completionTokens = isset($usage['completion_tokens']) && (is_int($usage['completion_tokens']) || is_string($usage['completion_tokens']))
            ? (int) $usage['completion_tokens']
            : 0;

        $totalTokens = isset($usage['total_tokens']) && (is_int($usage['total_tokens']) || is_string($usage['total_tokens']))
            ? (int) $usage['total_tokens']
            : ($promptTokens + $completionTokens);

        $log->setPromptTokens($promptTokens);
        $log->setCompletionTokens($completionTokens);
        $log->setTotalTokens($totalTokens);
    }

    private function applyFailure(ChatCompletionLog $log, string $errorMessage): void
    {
        $log->setStatus('failed');
        $log->setErrorMessage($errorMessage);
        $log->setResponsePayload(null);
        $log->setPromptTokens(0);
        $log->setCompletionTokens(0);
        $log->setTotalTokens(0);
    }

    private function persist(ChatCompletionLog $log): void
    {
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
