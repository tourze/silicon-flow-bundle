<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Request;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Exception\ApiException;
use Tourze\SiliconFlowBundle\Request\CreateChatMessagesRequest;

#[CoversClass(CreateChatMessagesRequest::class)]
final class CreateChatMessagesRequestTest extends RequestTestCase
{
    public function testBuildRequestOptions(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('primary');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $request = new CreateChatMessagesRequest(
            $config,
            'deepseek-chat',
            [
                ['role' => 'user', 'content' => 'Hello'],
            ],
            1024,
            ['temperature' => 0.6],
        );

        $options = $request->getRequestOptions();
        self::assertArrayHasKey('headers', $options);
        $headers = $options['headers'];
        self::assertIsArray($headers);

        self::assertSame('Bearer test-token', $headers['Authorization'] ?? null);
        self::assertSame('application/json', $headers['Content-Type'] ?? null);
        self::assertSame(1024, $request->getMaxTokens());
        self::assertSame('deepseek-chat', $request->getModel());
        $messages = $request->getMessages();
        self::assertCount(1, $messages);
        self::assertArrayHasKey(0, $messages);
        self::assertArrayHasKey('content', $messages[0]);
        self::assertSame('Hello', $messages[0]['content']);
        self::assertFalse($request->expectsStream());
    }

    public function testStreamOptionSwitchesAcceptHeader(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('stream');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('stream-token');

        $request = new CreateChatMessagesRequest(
            $config,
            'deepseek-chat',
            [
                ['role' => 'user', 'content' => 'Hello'],
            ],
            512,
            ['stream' => true],
        );

        $options = $request->getRequestOptions();
        self::assertArrayHasKey('headers', $options);
        $headers = $options['headers'];
        self::assertIsArray($headers);

        self::assertSame('text/event-stream', $headers['Accept'] ?? null);
        self::assertTrue($request->expectsStream());
    }

    public function testValidateFailsWhenMessagesEmpty(): void
    {
        $this->expectException(ApiException::class);

        $config = new SiliconFlowConfig();
        $config->setName('invalid');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('token');

        $request = new CreateChatMessagesRequest($config, 'model', [], 1);

        $request->validate();
    }

    public function testParseResponseWithJsonResponse(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('test');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $request = new CreateChatMessagesRequest(
            $config,
            'deepseek-chat',
            [
                ['role' => 'user', 'content' => 'Hello'],
            ],
            1024,
        );

        $response = json_encode([
            'id' => 'msg-123',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                ['type' => 'text', 'text' => 'Hello! How can I help you?']
            ],
            'model' => 'deepseek-chat',
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 10,
                'output_tokens' => 15
            ]
        ]);
        if ($response === false) {
            throw new \RuntimeException('Failed to encode test JSON response');
        }

        $result = $request->parseResponse($response);

        self::assertSame('msg-123', $result['id']);
        self::assertSame('message', $result['type']);
        self::assertSame('assistant', $result['role']);
        self::assertSame('deepseek-chat', $result['model']);
        self::assertSame('end_turn', $result['stop_reason']);
        self::assertIsArray($result['content']);
        self::assertArrayHasKey(0, $result['content']);
        $firstContent = $result['content'][0];
        self::assertIsArray($firstContent);
        self::assertArrayHasKey('text', $firstContent);
        self::assertSame('Hello! How can I help you?', $firstContent['text']);
        self::assertIsArray($result['usage']);
    }

    public function testParseResponseWithInvalidJson(): void
    {
        $this->expectException(\Tourze\SiliconFlowBundle\Exception\ApiException::class);

        $config = new SiliconFlowConfig();
        $config->setName('test');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $request = new CreateChatMessagesRequest(
            $config,
            'deepseek-chat',
            [
                ['role' => 'user', 'content' => 'Hello'],
            ],
            1024,
        );

        $request->parseResponse('invalid json');
    }

    public function testToArray(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('test');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-token');

        $request = new CreateChatMessagesRequest(
            $config,
            'deepseek-chat',
            [
                ['role' => 'user', 'content' => 'Hello'],
                ['role' => 'assistant', 'content' => 'Hi there!']
            ],
            1024,
            ['temperature' => 0.6, 'top_p' => 0.9],
        );

        $result = $request->toArray();

        self::assertSame('deepseek-chat', $result['model']);
        self::assertSame([
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'assistant', 'content' => 'Hi there!']
        ], $result['messages']);
        self::assertSame(1024, $result['max_tokens']);
        self::assertSame(0.6, $result['temperature']);
        self::assertSame(0.9, $result['top_p']);
    }

    public function testExpectsStream(): void
    {
        $config = new SiliconFlowConfig();
        $messages = [['role' => 'user', 'content' => 'Hello']];

        $request = new CreateChatMessagesRequest($config, 'deepseek-chat', $messages, 1024, ['stream' => true]);

        $this->assertTrue($request->expectsStream());
    }
}
