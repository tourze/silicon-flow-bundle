<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowModelRepository;

#[ORM\Entity(repositoryClass: SiliconFlowModelRepository::class)]
#[ORM\Table(name: 'silicon_flow_models', options: ['comment' => 'SiliconFlow 模型列表缓存'])]
class SiliconFlowModel implements \Stringable
{
    use TimestampableAware;

    public const TYPE_TEXT = 'text';
    public const TYPE_IMAGE = 'image';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_VIDEO = 'video';

    /**
     * @var mixed Auto-generated ID by Doctrine
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private mixed $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '模型标识'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $modelId = '';

    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '模型类型', 'default' => self::TYPE_TEXT])]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::TYPE_TEXT, self::TYPE_IMAGE, self::TYPE_AUDIO, self::TYPE_VIDEO])]
    private string $type = self::TYPE_TEXT;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '对象类型'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $objectType = 'model';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否有效'])]
    #[Assert\Type(type: 'bool')]
    private bool $isActive = true;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '附加元数据'])]
    #[Assert\Type(type: 'array')]
    private ?array $metadata = null;

    public function __toString(): string
    {
        return $this->modelId;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getModelId(): string
    {
        return $this->modelId;
    }

    public function setModelId(string $modelId): void
    {
        $this->modelId = $modelId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        if (!in_array($type, self::getSupportedTypes(), true)) {
            throw new InvalidArgumentException(sprintf('Unsupported SiliconFlow model type: %s', $type));
        }

        $this->type = $type;
    }

    public function getObjectType(): string
    {
        return $this->objectType;
    }

    public function setObjectType(string $objectType): void
    {
        $this->objectType = $objectType;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, mixed>|null $metadata
     */
    public function setMetadata(?array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * @return string[]
     */
    public static function getSupportedTypes(): array
    {
        return [
            self::TYPE_TEXT,
            self::TYPE_IMAGE,
            self::TYPE_AUDIO,
            self::TYPE_VIDEO,
        ];
    }
}
