<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

final class SiliconFlowExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        // AutoExtension 会自动加载 services.yaml 和环境特定的配置文件
        parent::load($configs, $container);

        // 直接从环境变量读取配置，遵循12 Factor App原则
        $apiKey = $_ENV['SILICON_FLOW_API_KEY'] ?? '';
        if (!is_string($apiKey)) {
            $apiKey = '';
        }
        $container->setParameter('tourze_silicon_flow.api_key', trim($apiKey));

        $baseUrl = $_ENV['SILICON_FLOW_BASE_URL'] ?? 'https://api.siliconflow.cn';
        if (!is_string($baseUrl)) {
            $baseUrl = 'https://api.siliconflow.cn';
        }
        $container->setParameter('tourze_silicon_flow.base_url', trim($baseUrl));

        $requestTimeout = $_ENV['SILICON_FLOW_REQUEST_TIMEOUT'] ?? 30;
        if (!is_numeric($requestTimeout)) {
            $requestTimeout = 30;
        }
        $container->setParameter('tourze_silicon_flow.request_timeout', (int) $requestTimeout);
    }
}
