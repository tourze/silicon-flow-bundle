<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Request;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Request\CreateChatCompletionRequest;

/**
 * ChatCompletion 请求测试
 */
#[CoversClass(CreateChatCompletionRequest::class)]
class CreateChatCompletionRequestTest extends TestCase
{
    private SiliconFlowConfig $config;

    protected function setUp(): void
    {
        $this->config = new SiliconFlowConfig();
        $this->config->setName('test');
        $this->config->setBaseUrl('https://api.siliconflow.cn');
        $this->config->setApiToken('test-token');
    }

    public function testConstruct(): void
    {
        $messages = [['role' => 'user', 'content' => 'Hello']];
        $request = new CreateChatCompletionRequest($this->config, 'test-model', $messages);
        $this->assertInstanceOf(CreateChatCompletionRequest::class, $request);
    }

    public function testGetMethod(): void
    {
        $messages = [['role' => 'user', 'content' => 'Hello']];
        $request = new CreateChatCompletionRequest($this->config, 'test-model', $messages);
        $this->assertSame('POST', $request->getRequestMethod());
    }

    public function testGetPath(): void
    {
        $messages = [['role' => 'user', 'content' => 'Hello']];
        $request = new CreateChatCompletionRequest($this->config, 'test-model', $messages);
        $this->assertSame('/v1/chat/completions', $request->getRequestPath());
    }

    public function testGetHeaders(): void
    {
        $messages = [['role' => 'user', 'content' => 'Hello']];
        $request = new CreateChatCompletionRequest($this->config, 'test-model', $messages);
        $options = $request->getRequestOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('headers', $options);

        $headers = $options['headers'];
        $this->assertIsArray($headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertSame('Bearer test-token', $headers['Authorization']);
    }

    public function testGetBody(): void
    {
        $messages = [
            ['role' => 'user', 'content' => 'Hello, how are you?']
        ];

        $request = new CreateChatCompletionRequest($this->config, 'claude-3-5-sonnet-20241022', $messages);
        $options = $request->getRequestOptions();

        $this->assertArrayHasKey('json', $options);
        $decoded = $options['json'];
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('model', $decoded);
        $this->assertArrayHasKey('messages', $decoded);
        $this->assertSame('claude-3-5-sonnet-20241022', $decoded['model']);
        $this->assertSame($messages, $decoded['messages']);
    }

    public function testGetBodyWithOptionalParams(): void
    {
        $messages = [
            ['role' => 'user', 'content' => 'Hello']
        ];

        $options = [
            'max_tokens' => 1000,
            'temperature' => 0.7,
            'stream' => true,
        ];

        $request = new CreateChatCompletionRequest($this->config, 'claude-3-5-haiku-20241022', $messages, $options);
        $requestOptions = $request->getRequestOptions();

        $this->assertArrayHasKey('json', $requestOptions);
        $decoded = $requestOptions['json'];
        $this->assertIsArray($decoded);

        $this->assertArrayHasKey('max_tokens', $decoded);
        $this->assertArrayHasKey('temperature', $decoded);
        $this->assertArrayHasKey('stream', $decoded);
        $this->assertSame(1000, $decoded['max_tokens']);
        $this->assertSame(0.7, $decoded['temperature']);
        $this->assertTrue($decoded['stream']);
    }
}