<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowConversationRepository;

#[ORM\Entity(repositoryClass: SiliconFlowConversationRepository::class)]
#[ORM\Table(name: 'silicon_flow_conversations', options: ['comment' => 'SiliconFlow 对话记录'])]
class SiliconFlowConversation implements \Stringable
{
    use TimestampableAware;

    public const TYPE_SINGLE = 'single';
    public const TYPE_CONTINUOUS = 'continuous';

    /**
     * @var mixed Auto-generated ID by Doctrine
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private mixed $id = null;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '对话类型'])]
    #[Assert\Choice(choices: [self::TYPE_SINGLE, self::TYPE_CONTINUOUS])]
    private string $conversationType = self::TYPE_SINGLE;

    #[ORM\Column(type: Types::STRING, length: 150, options: ['comment' => '使用模型'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private string $model = '';

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '问题内容'])]
    #[Assert\NotBlank]
    private string $question = '';

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '返回答案'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $answer = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '推理内容'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $reasoningContent = null;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(name: 'sender_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull]
    private UserInterface $sender;

    // 上下文
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'context_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?self $context = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '上下文快照'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $contextSnapshot = null;

    public function __toString(): string
    {
        return mb_strimwidth($this->question, 0, 50, '...');
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getConversationType(): string
    {
        return $this->conversationType;
    }

    public function setConversationType(string $conversationType): void
    {
        $this->conversationType = $conversationType;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(?string $answer): void
    {
        $this->answer = $answer;
    }

    public function getReasoningContent(): ?string
    {
        return $this->reasoningContent;
    }

    public function setReasoningContent(?string $reasoningContent): void
    {
        $this->reasoningContent = $reasoningContent;
    }

    public function getSender(): UserInterface
    {
        return $this->sender;
    }

    public function setSender(UserInterface $sender): void
    {
        $this->sender = $sender;
    }

    public function getContext(): ?self
    {
        return $this->context;
    }

    public function setContext(?self $context): void
    {
        $this->context = $context;
    }

    public function getContextSnapshot(): ?string
    {
        return $this->contextSnapshot;
    }

    public function setContextSnapshot(?string $contextSnapshot): void
    {
        $this->contextSnapshot = $contextSnapshot;
    }
}
