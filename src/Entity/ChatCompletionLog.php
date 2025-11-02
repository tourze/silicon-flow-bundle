<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\SiliconFlowBundle\Repository\ChatCompletionLogRepository;

/**
 * SiliconFlow 聊天完成日志实体
 *
 * 记录所有聊天完成请求的详细信息，用于：
 * - 请求追踪和审计
 * - 成本计算和使用量统计
 * - 错误分析和问题排查
 * - 性能监控和优化
 */
#[ORM\Entity(repositoryClass: ChatCompletionLogRepository::class)]
#[ORM\Table(name: 'silicon_flow_chat_completion_logs', options: ['comment' => 'SiliconFlow Chat Completions 调用日志'])]
class ChatCompletionLog implements \Stringable
{
    use TimestampableAware;

    /**
     * @var mixed Auto-generated ID by Doctrine
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private mixed $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => 'SiliconFlow 返回的请求 ID'])]
    #[Assert\Length(max: 100)]
    private ?string $requestId = null;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '模型名称'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $model = '';

    #[ORM\Column(type: Types::STRING, length: 20, options: ['default' => 'success', 'comment' => '请求状态'])]
    #[Assert\Length(max: 20)]
    private string $status = 'success';

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '原始请求体'])]
    #[Assert\Type(type: 'array')]
    #[Assert\Count(min: 1)]
    private array $requestPayload = [];

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '原始响应体'])]
    #[Assert\Type(type: 'array')]
    #[Assert\Count(min: 1)]
    private ?array $responsePayload = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '错误信息'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $errorMessage = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => 'Prompt Tokens'])]
    #[Assert\PositiveOrZero]
    private int $promptTokens = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => 'Completion Tokens'])]
    #[Assert\PositiveOrZero]
    private int $completionTokens = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '总 Token 数'])]
    #[Assert\PositiveOrZero]
    private int $totalTokens = 0;

    public function __construct()
    {
    }

    public function __toString(): string
    {
        return $this->model;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function setRequestId(?string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRequestPayload(): array
    {
        return $this->requestPayload;
    }

    /**
     * @param array<string, mixed> $requestPayload
     */
    public function setRequestPayload(array $requestPayload): void
    {
        $this->requestPayload = $requestPayload;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getResponsePayload(): ?array
    {
        return $this->responsePayload;
    }

    /**
     * @param array<string, mixed>|null $responsePayload
     */
    public function setResponsePayload(?array $responsePayload): void
    {
        $this->responsePayload = $responsePayload;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function getPromptTokens(): int
    {
        return $this->promptTokens;
    }

    public function setPromptTokens(int $promptTokens): void
    {
        $this->promptTokens = $promptTokens;
    }

    public function getCompletionTokens(): int
    {
        return $this->completionTokens;
    }

    public function setCompletionTokens(int $completionTokens): void
    {
        $this->completionTokens = $completionTokens;
    }

    public function getTotalTokens(): int
    {
        return $this->totalTokens;
    }

    public function setTotalTokens(int $totalTokens): void
    {
        $this->totalTokens = $totalTokens;
    }
}
