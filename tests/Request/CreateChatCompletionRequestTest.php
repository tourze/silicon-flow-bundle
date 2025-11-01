<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\SiliconFlowBundle\Request\CreateChatCompletionRequest;

/**
 * ChatCompletion 请求测试
 */
#[CoversClass(CreateChatCompletionRequest::class)]
class CreateChatCompletionRequestTest extends RequestTestCase
{
    public function testConstruct(): void
    {
        $request = new CreateChatCompletionRequest();
        $this->assertInstanceOf(CreateChatCompletionRequest::class, $request);
    }

    public function testGetMethod(): void
    {
        $request = new CreateChatCompletionRequest();
        $this->assertSame('POST', $request->getMethod());
    }

    public function testGetPath(): void
    {
        $request = new CreateChatCompletionRequest();
        $this->assertSame('/v1/chat/completions', $request->getPath());
    }

    public function testGetHeaders(): void
    {
        $request = new CreateChatCompletionRequest();
        $headers = $request->getHeaders();

        $this->assertIsArray($headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBody(): void
    {
        $messages = [
            ['role' => 'user', 'content' => 'Hello, how are you?']
        ];

        $request = new CreateChatCompletionRequest();
        $request->setModel('claude-3-5-sonnet-20241022');
        $request->setMessages($messages);

        $body = $request->getBody();
        $this->assertIsString($body);

        $decoded = json_decode($body, true);
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

        $request = new CreateChatCompletionRequest();
        $request->setModel('claude-3-5-haiku-20241022');
        $request->setMessages($messages);
        $request->setMaxTokens(1000);
        $request->setTemperature(0.7);
        $request->setStream(true);

        $body = $request->getBody();
        $decoded = json_decode($body, true);

        $this->assertArrayHasKey('max_tokens', $decoded);
        $this->assertArrayHasKey('temperature', $decoded);
        $this->assertArrayHasKey('stream', $decoded);
        $this->assertSame(1000, $decoded['max_tokens']);
        $this->assertSame(0.7, $decoded['temperature']);
        $this->assertTrue($decoded['stream']);
    }
}