<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Entity;

use BizUserBundle\Entity\BizUser;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowVideoGenerationRepository;

#[ORM\Entity(repositoryClass: SiliconFlowVideoGenerationRepository::class)]
#[ORM\Table(name: 'silicon_flow_video_generations', options: ['comment' => 'SiliconFlow 视频生成记录'])]
class SiliconFlowVideoGeneration implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private mixed $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => 'SiliconFlow 返回的请求 ID'])]
    #[Assert\Length(max: 100)]
    #[IndexColumn]
    private ?string $requestId = null;

    #[ORM\Column(type: Types::STRING, length: 150, options: ['comment' => '使用模型'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private string $model = '';

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '视频生成提示词'])]
    #[Assert\NotBlank]
    private string $prompt = '';

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '反向提示词'])]
    #[Assert\Type(type: 'string')]
    private ?string $negativePrompt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '输入图像 Base64 或 URL'])]
    #[Assert\Type(type: 'string')]
    private ?string $image = null;

    #[ORM\Column(type: Types::STRING, length: 30, nullable: true, options: ['comment' => '图像尺寸(widthxheight)'])]
    #[Assert\Length(max: 30)]
    private ?string $imageSize = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '随机种子'])]
    #[Assert\PositiveOrZero]
    private ?int $seed = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => 5, 'comment' => '推理步数'])]
    #[Assert\Type(type: 'int')]
    #[Assert\Positive]
    private ?int $numInferenceSteps = 5;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '原始请求体'])]
    #[Assert\Type(type: 'array')]
    #[Assert\Count(min: 1)]
    private array $requestPayload = [];

    #[ORM\ManyToOne(targetEntity: BizUser::class)]
    #[ORM\JoinColumn(name: 'sender_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull]
    private ?BizUser $sender = null;

    #[ORM\Column(type: Types::STRING, length: 30, nullable: true, options: ['comment' => '任务状态'])]
    #[Assert\Length(max: 30)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '状态原因或错误信息'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $statusReason = null;

    /**
     * @var string[]|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '生成视频链接集合'])]
    #[Assert\Type(type: 'array')]
    #[Assert\Count(min: 1)]
    private ?array $videoUrls = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['comment' => '推理耗时(秒)'])]
    #[Assert\PositiveOrZero]
    private ?float $inferenceTime = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '响应返回的种子'])]
    #[Assert\PositiveOrZero]
    private ?int $resultSeed = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '原始响应体'])]
    #[Assert\Type(type: 'array')]
    #[Assert\Count(min: 1)]
    private ?array $responsePayload = null;

    public function __toString(): string
    {
        return sprintf('%s: %s', $this->model, mb_strimwidth($this->prompt, 0, 40, '...'));
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

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function setPrompt(string $prompt): void
    {
        $this->prompt = $prompt;
    }

    public function getNegativePrompt(): ?string
    {
        return $this->negativePrompt;
    }

    public function setNegativePrompt(?string $negativePrompt): void
    {
        $this->negativePrompt = $negativePrompt;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getImageSize(): ?string
    {
        return $this->imageSize;
    }

    public function setImageSize(?string $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getSeed(): ?int
    {
        return $this->seed;
    }

    public function setSeed(?int $seed): void
    {
        $this->seed = $seed;
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

    public function getSender(): ?BizUser
    {
        return $this->sender;
    }

    public function setSender(?BizUser $sender): void
    {
        $this->sender = $sender;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getStatusReason(): ?string
    {
        return $this->statusReason;
    }

    public function setStatusReason(?string $statusReason): void
    {
        $this->statusReason = $statusReason;
    }

    /**
     * @return string[]|null
     */
    public function getVideoUrls(): ?array
    {
        return $this->videoUrls;
    }

    /**
     * @param string[]|null $videoUrls
     */
    public function setVideoUrls(?array $videoUrls): void
    {
        $this->videoUrls = $videoUrls;
    }

    public function getInferenceTime(): ?float
    {
        return $this->inferenceTime;
    }

    public function setInferenceTime(?float $inferenceTime): void
    {
        $this->inferenceTime = $inferenceTime;
    }

    public function getResultSeed(): ?int
    {
        return $this->resultSeed;
    }

    public function setResultSeed(?int $resultSeed): void
    {
        $this->resultSeed = $resultSeed;
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

    public function getNumInferenceSteps(): ?int
    {
        return $this->numInferenceSteps;
    }

    public function setNumInferenceSteps(?int $numInferenceSteps): void
    {
        $this->numInferenceSteps = $numInferenceSteps;
    }

    /**
     * Alias for getSender() for backward compatibility
     */
    public function getUser(): ?BizUser
    {
        return $this->getSender();
    }

    /**
     * Alias for setSender() for backward compatibility
     */
    public function setUser(?BizUser $user): void
    {
        $this->setSender($user);
    }
}
