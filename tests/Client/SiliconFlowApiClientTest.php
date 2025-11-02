<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Client;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Symfony\Component\HttpClient\Response\MockResponse;
use Tourze\SiliconFlowBundle\Client\SiliconFlowApiClient;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Exception\ApiException;
use Tourze\SiliconFlowBundle\Request\CreateChatCompletionRequest;

#[CoversClass(SiliconFlowApiClient::class)]
#[RunTestsInSeparateProcesses]
final class SiliconFlowApiClientTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // No setup needed
    }

    public function testCreateChatCompletionDecodesJson(): void
    {
        $capturedOptions = [];
        $httpClient = new MockHttpClient(static function (string $method, string $url, array $options) use (&$capturedOptions): MockResponse {
            $capturedOptions = $options;

            return new MockResponse(json_encode([
                'id' => 'chatcmpl-json',
                'model' => 'deepseek-chat',
                'created' => 1700000000,
                'choices' => [],
                'usage' => [
                    'prompt_tokens' => 1,
                    'completion_tokens' => 1,
                    'total_tokens' => 2,
                ],
            ], JSON_THROW_ON_ERROR));
        });

        $client = new SiliconFlowApiClient($httpClient, new NullLogger(), 30);

        $config = new SiliconFlowConfig();
        $config->setName('primary');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('test-key');

        $request = new CreateChatCompletionRequest(
            $config,
            'deepseek-chat',
            [
                ['role' => 'user', 'content' => 'hi'],
            ],
        );

        $response = $client->request($request);

        self::assertSame('chatcmpl-json', $response['id']);
        self::assertSame('deepseek-chat', $response['model']);
        self::assertSame(1700000000, $response['created']);
        self::assertIsArray($response['usage']);
        self::assertSame(1, $response['usage']['prompt_tokens']);

        $headers = $capturedOptions['headers'] ?? [];
        self::assertIsArray($headers);
        if (array_is_list($headers)) {
            $authorization = null;
            foreach ($headers as $header) {
                self::assertIsString($header);
                if (str_starts_with($header, 'Authorization:')) {
                    $authorization = trim(substr($header, strlen('Authorization:')));
                    break;
                }
            }
            self::assertIsString($authorization);
            self::assertSame('bearer test-key', strtolower($authorization));
        } else {
            $normalizedHeaders = array_change_key_case($headers, CASE_LOWER);
            $authValue = $normalizedHeaders['authorization'] ?? '';
            self::assertIsString($authValue);
            self::assertSame('bearer test-key', strtolower($authValue));
        }

        $payload = $request->toArray();
        self::assertSame('deepseek-chat', $payload['model']);
        self::assertIsArray($payload['messages']);
        $firstMessage = $payload['messages'][0];
        self::assertIsArray($firstMessage);
        self::assertSame('hi', $firstMessage['content'] ?? null);
    }

    public function testCreateChatCompletionParsesStreamResponse(): void
    {
        $capturedOptions = [];
        $sseBody = implode("\n\n", [
            'data: {"id":"chatcmpl-stream","model":"deepseek-chat","created":1700000000,"choices":[{"index":0,"delta":{"role":"assistant","content":"Hello"},"finish_reason":null}]}',
            'data: {"id":"chatcmpl-stream","model":"deepseek-chat","created":1700000000,"choices":[{"index":0,"delta":{"content":" world"},"finish_reason":"stop"}],"usage":{"prompt_tokens":12,"completion_tokens":8,"total_tokens":20}}',
            'data: [DONE]',
            '',
        ]);

        $httpClient = new MockHttpClient(static function (string $method, string $url, array $options) use (&$capturedOptions, $sseBody): MockResponse {
            $capturedOptions = $options;

            return new MockResponse($sseBody, [
                'response_headers' => ['content-type' => 'text/event-stream'],
            ]);
        });

        $client = new SiliconFlowApiClient($httpClient, new NullLogger(), 30);

        $config = new SiliconFlowConfig();
        $config->setName('stream');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('stream-key');

        $request = new CreateChatCompletionRequest(
            $config,
            'deepseek-chat',
            [
                ['role' => 'user', 'content' => 'start'],
            ],
            ['stream' => true],
        );

        $response = $client->request($request);

        self::assertSame('chatcmpl-stream', $response['id']);
        self::assertSame('deepseek-chat', $response['model']);
        self::assertIsArray($response['usage']);
        self::assertSame(12, $response['usage']['prompt_tokens']);
        self::assertSame(8, $response['usage']['completion_tokens']);
        self::assertIsArray($response['choices']);
        self::assertIsArray($response['choices'][0]);
        self::assertIsArray($response['choices'][0]['message']);
        self::assertSame('Hello world', $response['choices'][0]['message']['content']);
        self::assertSame('stop', $response['choices'][0]['finish_reason']);

        $headers = $capturedOptions['headers'] ?? [];
        self::assertIsArray($headers);
        if (array_is_list($headers)) {
            self::assertContains('Accept: text/event-stream', $headers);
        } else {
            $normalizedHeaders = array_change_key_case($headers, CASE_LOWER);
            self::assertSame('text/event-stream', $normalizedHeaders['accept'] ?? null);
        }
        self::assertTrue((bool) ($request->toArray()['stream'] ?? false));
    }

    public function testCreateChatCompletionThrowsWhenApiKeyMissing(): void
    {
        $httpClient = new MockHttpClient(static fn (): MockResponse => new MockResponse('{}'));

        $client = new SiliconFlowApiClient($httpClient, new NullLogger(), 30);

        $config = new SiliconFlowConfig();
        $config->setName('invalid');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('');

        $this->expectException(ApiException::class);

        $request = new CreateChatCompletionRequest(
            $config,
            'deepseek-chat',
            [
                ['role' => 'user', 'content' => 'hi'],
            ],
        );

        $client->request($request);
    }
}
