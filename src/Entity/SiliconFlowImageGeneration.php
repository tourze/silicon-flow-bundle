<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Entity;

use BizUserBundle\Entity\BizUser;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowImageGenerationRepository;

#[ORM\Entity(repositoryClass: SiliconFlowImageGenerationRepository::class)]
#[ORM\Table(name: 'silicon_flow_image_generations', options: ['comment' => 'SiliconFlow 图片生成记录'])]
class SiliconFlowImageGeneration implements \Stringable
{
    use TimestampableAware;

    /**
     * @var mixed Auto-generated ID by Doctrine
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private mixed $id = null;

    #[ORM\Column(type: Types::STRING, length: 150, options: ['comment' => '使用模型'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private string $model = '';

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '正向提示词'])]
    #[Assert\NotBlank]
    private string $prompt = '';

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '反向提示词'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $negativePrompt = null;

    #[ORM\Column(type: Types::STRING, length: 30, nullable: true, options: ['comment' => '图像尺寸(widthxheight)'])]
    #[Assert\Length(max: 30)]
    private ?string $imageSize = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 1, 'comment' => '批量数量'])]
    #[Assert\Positive]
    private int $batchSize = 1;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '随机种子'])]
    #[Assert\Type(type: 'int')]
    #[Assert\Positive]
    private ?int $seed = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '采样步数'])]
    #[Assert\Type(type: 'int')]
    #[Assert\Positive]
    private ?int $numInferenceSteps = null;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '请求原始载荷'])]
    #[Assert\Type(type: 'array')]
    #[Assert\Count(min: 1)]
    private array $requestPayload = [];

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '响应原始载荷'])]
    #[Assert\Type(type: 'array')]
    #[Assert\Count(min: 1)]
    private ?array $responsePayload = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '生成图片链接'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $imageUrls = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['comment' => '推理耗时(秒)'])]
    #[Assert\Type(type: 'float')]
    #[Assert\PositiveOrZero]
    private ?float $inferenceTime = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '响应返回的种子'])]
    #[Assert\Type(type: 'int')]
    #[Assert\Positive]
    private ?int $responseSeed = null;

    #[ORM\ManyToOne(targetEntity: BizUser::class)]
    #[ORM\JoinColumn(name: 'sender_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull]
    private BizUser $sender;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '响应状态'])]
    #[Assert\Length(max: 50)]
    private ?string $status = null;

    public function __toString(): string
    {
        return sprintf('%s: %s', $this->model, mb_strimwidth($this->prompt, 0, 40, '...'));
    }

    public function getId(): mixed
    {
        return $this->id;
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

    public function getImageSize(): ?string
    {
        return $this->imageSize;
    }

    public function setImageSize(?string $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    public function setBatchSize(int $batchSize): void
    {
        $this->batchSize = $batchSize;
    }

    public function getSeed(): ?int
    {
        return $this->seed;
    }

    public function setSeed(?int $seed): void
    {
        $this->seed = $seed;
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

    /**
     * @return string|null JSON string containing array of image URLs
     */
    public function getImageUrls(): ?string
    {
        return $this->imageUrls;
    }

    public function setImageUrls(?string $imageUrls): void
    {
        $this->imageUrls = $imageUrls;
    }

    public function getInferenceTime(): ?float
    {
        return $this->inferenceTime;
    }

    public function setInferenceTime(?float $inferenceTime): void
    {
        $this->inferenceTime = $inferenceTime;
    }

    public function getResponseSeed(): ?int
    {
        return $this->responseSeed;
    }

    public function setResponseSeed(?int $responseSeed): void
    {
        $this->responseSeed = $responseSeed;
    }

    public function getSender(): BizUser
    {
        return $this->sender;
    }

    public function setSender(BizUser $sender): void
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
}
