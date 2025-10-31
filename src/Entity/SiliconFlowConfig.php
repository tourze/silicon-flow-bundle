<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\SiliconFlowBundle\Repository\SiliconFlowConfigRepository;

/**
 * SiliconFlow 配置实体
 *
 * 管理 SiliconFlow API 的接入配置，支持：
 * - 多环境配置管理
 * - 优先级控制和自动选择
 * - 认证信息和请求参数配置
 * - 启用状态管理和版本控制
 */
#[ORM\Entity(repositoryClass: SiliconFlowConfigRepository::class)]
#[ORM\Table(name: 'silicon_flow_configs', options: ['comment' => 'SiliconFlow 接入配置'])]
class SiliconFlowConfig implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '配置名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => 'API 基础地址'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private string $baseUrl = 'https://api.siliconflow.cn';

    #[ORM\Column(type: Types::TEXT, options: ['comment' => 'API Token'])]
    #[Assert\NotBlank]
    private string $apiToken = '';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否启用'])]
    #[Assert\Type(type: 'bool')]
    private bool $isActive = true;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '优先级，越大越优先'])]
    #[Assert\Type(type: 'int')]
    #[Assert\PositiveOrZero]
    private int $priority = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $description = null;

    public function __construct()
    {
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function getApiToken(): string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken): void
    {
        $this->apiToken = $apiToken;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function activate(): void
    {
        $this->setIsActive(true);
    }

    public function deactivate(): void
    {
        $this->setIsActive(false);
    }
}
