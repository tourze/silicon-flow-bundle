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
        self::assertInstanceOf(CreateChatCompletionRequest::class, $request);
    }

    public function testGetMethod(): void
    {
        $messages = [['role' => 'user', 'content' => 'Hello']];
        $request = new CreateChatCompletionRequest($this->config, 'test-model', $messages);
        self::assertSame('POST', $request->getRequestMethod());
    }

    public function testGetPath(): void
    {
        $messages = [['role' => 'user', 'content' => 'Hello']];
        $request = new CreateChatCompletionRequest($this->config, 'test-model', $messages);
        self::assertSame('/v1/chat/completions', $request->getRequestPath());
    }

    public function testGetHeaders(): void
    {
        $messages = [['role' => 'user', 'content' => 'Hello']];
        $request = new CreateChatCompletionRequest($this->config, 'test-model', $messages);
        $options = $request->getRequestOptions();

        self::assertIsArray($options);
        self::assertArrayHasKey('headers', $options);

        $headers = $options['headers'];
        self::assertIsArray($headers);
        self::assertArrayHasKey('Content-Type', $headers);
        self::assertSame('application/json', $headers['Content-Type']);
        self::assertArrayHasKey('Authorization', $headers);
        self::assertSame('Bearer test-token', $headers['Authorization']);
    }

    public function testGetBody(): void
    {
        $messages = [
            ['role' => 'user', 'content' => 'Hello, how are you?']
        ];

        $request = new CreateChatCompletionRequest($this->config, 'claude-3-5-sonnet-20241022', $messages);
        $options = $request->getRequestOptions();

        self::assertArrayHasKey('json', $options);
        $decoded = $options['json'];
        self::assertIsArray($decoded);
        self::assertArrayHasKey('model', $decoded);
        self::assertArrayHasKey('messages', $decoded);
        self::assertSame('claude-3-5-sonnet-20241022', $decoded['model']);
        self::assertSame($messages, $decoded['messages']);
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

        self::assertArrayHasKey('json', $requestOptions);
        $decoded = $requestOptions['json'];
        self::assertIsArray($decoded);

        self::assertArrayHasKey('max_tokens', $decoded);
        self::assertArrayHasKey('temperature', $decoded);
        self::assertArrayHasKey('stream', $decoded);
        self::assertSame(1000, $decoded['max_tokens']);
        self::assertSame(0.7, $decoded['temperature']);
        self::assertTrue($decoded['stream']);
    }
}