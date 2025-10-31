<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\SiliconFlowBundle\DependencyInjection\SiliconFlowExtension;

/**
 * SiliconFlow Extension 测试
 */
#[CoversClass(SiliconFlowExtension::class)]
class SiliconFlowExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private SiliconFlowExtension $extension;
    private ContainerBuilder $container;
    /** @var array<string, string> */
    private array $originalEnv = [];

    protected function setUp(): void
    {
        $this->extension = new SiliconFlowExtension();
        $this->container = new ContainerBuilder();

        // 备份原始环境变量
        $this->originalEnv = [
            'SILICON_FLOW_API_KEY' => $_ENV['SILICON_FLOW_API_KEY'] ?? '',
            'SILICON_FLOW_BASE_URL' => $_ENV['SILICON_FLOW_BASE_URL'] ?? '',
            'SILICON_FLOW_REQUEST_TIMEOUT' => $_ENV['SILICON_FLOW_REQUEST_TIMEOUT'] ?? '',
        ];
    }

    protected function tearDown(): void
    {
        // 恢复原始环境变量
        foreach ($this->originalEnv as $key => $value) {
            if ($value === '') {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $value;
            }
        }
    }

    /**
     * 测试扩展实例化
     */
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(SiliconFlowExtension::class, $this->extension);
    }

    /**
     * 测试扩展类是否为final
     */
    public function testExtensionIsFinal(): void
    {
        $reflection = new \ReflectionClass(SiliconFlowExtension::class);
        $this->assertTrue($reflection->isFinal(), 'SiliconFlowExtension should be final');
    }

    /**
     * 测试getConfigDir方法
     */
    public function testGetConfigDir(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $method = $reflection->getMethod('getConfigDir');
        $method->setAccessible(true);

        $configDir = $method->invoke($this->extension);

        $this->assertIsString($configDir);
        $this->assertStringEndsWith('/Resources/config', $configDir);
    }

    /**
     * 测试加载默认配置（从环境变量）
     */
    public function testLoadDefaultConfiguration(): void
    {
        // 清理环境变量以测试默认值
        unset($_ENV['SILICON_FLOW_API_KEY']);
        unset($_ENV['SILICON_FLOW_BASE_URL']);
        unset($_ENV['SILICON_FLOW_REQUEST_TIMEOUT']);

        $configs = [];

        $this->extension->load($configs, $this->container);

        // 检查参数是否正确设置
        $this->assertTrue($this->container->hasParameter('tourze_silicon_flow.api_key'));
        $this->assertTrue($this->container->hasParameter('tourze_silicon_flow.base_url'));
        $this->assertTrue($this->container->hasParameter('tourze_silicon_flow.request_timeout'));

        // 检查默认值
        $this->assertSame('', $this->container->getParameter('tourze_silicon_flow.api_key'));
        $this->assertSame('https://api.siliconflow.cn', $this->container->getParameter('tourze_silicon_flow.base_url'));
        $this->assertSame(30, $this->container->getParameter('tourze_silicon_flow.request_timeout'));
    }

    /**
     * 测试从环境变量加载自定义配置
     */
    public function testLoadEnvironmentConfiguration(): void
    {
        $_ENV['SILICON_FLOW_API_KEY'] = 'sk-env123';
        $_ENV['SILICON_FLOW_BASE_URL'] = 'https://env.api.com';
        $_ENV['SILICON_FLOW_REQUEST_TIMEOUT'] = '60';

        $configs = [];

        $this->extension->load($configs, $this->container);

        // 检查环境变量值
        $this->assertSame('sk-env123', $this->container->getParameter('tourze_silicon_flow.api_key'));
        $this->assertSame('https://env.api.com', $this->container->getParameter('tourze_silicon_flow.base_url'));
        $this->assertSame(60, $this->container->getParameter('tourze_silicon_flow.request_timeout'));
    }

    /**
     * 测试部分环境变量配置
     */
    public function testLoadPartialEnvironmentConfiguration(): void
    {
        $_ENV['SILICON_FLOW_API_KEY'] = 'sk-partial';
        unset($_ENV['SILICON_FLOW_BASE_URL']);
        unset($_ENV['SILICON_FLOW_REQUEST_TIMEOUT']);

        $configs = [];

        $this->extension->load($configs, $this->container);

        // 部分配置应该使用默认值填充其他字段
        $this->assertSame('sk-partial', $this->container->getParameter('tourze_silicon_flow.api_key'));
        $this->assertSame('https://api.siliconflow.cn', $this->container->getParameter('tourze_silicon_flow.base_url'));
        $this->assertSame(30, $this->container->getParameter('tourze_silicon_flow.request_timeout'));
    }

    /**
     * 测试请求超时的类型转换
     */
    public function testRequestTimeoutTypeConversion(): void
    {
        $_ENV['SILICON_FLOW_REQUEST_TIMEOUT'] = '120';

        $configs = [];

        $this->extension->load($configs, $this->container);

        // 字符串应该被转换为整数
        $this->assertIsInt($this->container->getParameter('tourze_silicon_flow.request_timeout'));
        $this->assertSame(120, $this->container->getParameter('tourze_silicon_flow.request_timeout'));
    }

    /**
     * 测试无效的请求超时值
     */
    public function testInvalidRequestTimeoutValue(): void
    {
        $_ENV['SILICON_FLOW_REQUEST_TIMEOUT'] = 'invalid';

        $configs = [];

        $this->extension->load($configs, $this->container);

        // 无效值应该被转换为0（int类型转换结果）
        $this->assertSame(0, $this->container->getParameter('tourze_silicon_flow.request_timeout'));
    }

    /**
     * 测试空的环境变量值
     */
    public function testEmptyEnvironmentValues(): void
    {
        $_ENV['SILICON_FLOW_API_KEY'] = '';
        $_ENV['SILICON_FLOW_BASE_URL'] = '';
        $_ENV['SILICON_FLOW_REQUEST_TIMEOUT'] = '';

        $configs = [];

        $this->extension->load($configs, $this->container);

        $this->assertSame('', $this->container->getParameter('tourze_silicon_flow.api_key'));
        $this->assertSame('', $this->container->getParameter('tourze_silicon_flow.base_url'));
        $this->assertSame(0, $this->container->getParameter('tourze_silicon_flow.request_timeout'));
    }

    /**
     * 测试配置数组不影响环境变量读取
     */
    public function testConfigArrayIgnored(): void
    {
        $_ENV['SILICON_FLOW_API_KEY'] = 'sk-from-env';

        // 传入配置数组（应该被忽略）
        $configs = [
            [
                'api_key' => 'sk-from-config',
                'base_url' => 'https://config.api.com',
            ],
        ];

        $this->extension->load($configs, $this->container);

        // 应该使用环境变量值，忽略配置数组
        $this->assertSame('sk-from-env', $this->container->getParameter('tourze_silicon_flow.api_key'));
        $this->assertSame('https://api.siliconflow.cn', $this->container->getParameter('tourze_silicon_flow.base_url'));
    }
}