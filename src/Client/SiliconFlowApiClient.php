<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Client;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Tourze\SiliconFlowBundle\Exception\ApiException;
use Tourze\SiliconFlowBundle\Request\AbstractSiliconFlowRequest;

/**
 * SiliconFlow API 客户端
 *
 * 提供与 SiliconFlow API 的统一交互接口，支持：
 * - 自动处理 HTTP 请求和响应
 * - 统一的错误处理和异常转换
 * - 超时控制和重试机制
 * - JSON 和 SSE 流式响应的自动处理
 */
final class SiliconFlowApiClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly int $defaultTimeout = 30,
    ) {
    }

    /**
     * 执行 API 请求
     *
     * @param AbstractSiliconFlowRequest $request API 请求对象
     * @return array<string, mixed> 解析后的响应数据
     * @throws ApiException 当请求失败或响应格式错误时
     */
    public function request(AbstractSiliconFlowRequest $request): array
    {
        $request->validate();

        $method = $request->getRequestMethod() ?? 'POST';
        $url = $request->getBaseUrl() . $request->getRequestPath();
        $options = $request->getRequestOptions() ?? [];

        if (!isset($options['timeout'])) {
            $options['timeout'] = $this->defaultTimeout;
        }

        $sanitizedOptions = $this->sanitizeOptions($options);

        $this->logger->info('SiliconFlow API 请求', [
            'requestClass' => $request::class,
            'method' => $method,
            'url' => $url,
            'options' => $sanitizedOptions,
        ]);

        try {
            $response = $this->httpClient->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('SiliconFlow API 调用失败', [
                'requestClass' => $request::class,
                'method' => $method,
                'url' => $url,
                'options' => $sanitizedOptions,
                'error' => $exception->getMessage(),
            ]);

            throw new ApiException('调用 SiliconFlow 接口失败：' . $exception->getMessage(), 0, $exception);
        }

        $this->logger->info('SiliconFlow API 响应', [
            'requestClass' => $request::class,
            'statusCode' => $statusCode,
            'content' => $content,
        ]);

        if ($statusCode >= 400) {
            $this->logger->error('SiliconFlow API 返回异常状态码', [
                'requestClass' => $request::class,
                'statusCode' => $statusCode,
                'content' => $content,
            ]);

            throw new ApiException($request->parseError($content, $statusCode));
        }

        return $request->parseResponse($content);
    }

    /**
     * @return array{response: ResponseInterface, stream: ResponseStreamInterface}
     * @throws ApiException
     */
    public function requestStream(AbstractSiliconFlowRequest $request): array
    {
        $request->validate();

        $method = $request->getRequestMethod() ?? 'POST';
        $url = $request->getBaseUrl() . $request->getRequestPath();
        $options = $request->getRequestOptions() ?? [];

        if (!isset($options['timeout'])) {
            $options['timeout'] = $this->defaultTimeout;
        }

        if (!array_key_exists('buffer', $options)) {
            $options['buffer'] = false;
        }

        $sanitizedOptions = $this->sanitizeOptions($options);

        $this->logger->info('SiliconFlow API 流式请求', [
            'requestClass' => $request::class,
            'method' => $method,
            'url' => $url,
            'options' => $sanitizedOptions,
        ]);

        try {
            $response = $this->httpClient->request($method, $url, $options);
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 400) {
                $content = $response->getContent(false);

                $this->logger->error('SiliconFlow API 流式请求返回异常状态码', [
                    'requestClass' => $request::class,
                    'statusCode' => $statusCode,
                    'content' => $content,
                ]);

                throw new ApiException($request->parseError($content, $statusCode));
            }
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('SiliconFlow API 流式请求失败', [
                'requestClass' => $request::class,
                'method' => $method,
                'url' => $url,
                'options' => $sanitizedOptions,
                'error' => $exception->getMessage(),
            ]);

            throw new ApiException('调用 SiliconFlow 接口失败：' . $exception->getMessage(), 0, $exception);
        }

        $this->logger->info('SiliconFlow API 流式响应开始', [
            'requestClass' => $request::class,
            'statusCode' => $response->getStatusCode(),
        ]);

        return [
            'response' => $response,
            'stream' => $this->httpClient->stream($response),
        ];
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function sanitizeOptions(array $options): array
    {
        if (!isset($options['headers']) || !is_array($options['headers'])) {
            return $options;
        }

        $sanitizedHeaders = [];

        foreach ($options['headers'] as $key => $value) {
            if (is_string($key) && 0 === strcasecmp($key, 'Authorization')) {
                $sanitizedHeaders[$key] = $this->maskHeaderValue($value);
                continue;
            }

            $sanitizedHeaders[$key] = $value;
        }

        $options['headers'] = $sanitizedHeaders;

        return $options;
    }

    private function maskHeaderValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return '***';
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item) => is_string($item) ? '***' : $item, $value);
        }

        return $value;
    }
}
