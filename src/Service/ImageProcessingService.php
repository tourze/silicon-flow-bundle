<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tourze\FileStorageBundle\Service\FileService;

/**
 * 处理图片相关操作的服务类
 *
 * @phpstan-type UrlItem string|array{url: string}
 * @phpstan-type TransformedItem UrlItem|null
 */
final class ImageProcessingService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly FileService $fileService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    public function replaceResponseImageUrls(array $response): array
    {
        if (isset($response['data']) && is_array($response['data'])) {
            $response['data'] = $this->transformUrlArray($response['data']);
        }

        if (isset($response['images']) && is_array($response['images'])) {
            $response['images'] = $this->transformUrlArray($response['images']);
        }

        return $response;
    }

    /**
     * @param array<mixed> $items
     * @return array<int, string|array<string, mixed>>
     */
    public function transformUrlArray(array $items): array
    {
        $result = [];

        foreach ($items as $index => $item) {
            if (is_string($item)) {
                $result[$index] = $this->transferImageToLocal($item) ?? $item;
                continue;
            }

            if (is_array($item) && isset($item['url']) && is_string($item['url'])) {
                $localUrl = $this->transferImageToLocal($item['url']);
                if (null !== $localUrl) {
                    $item['url'] = $localUrl;
                }
                /** @var array<string, mixed> $item */
                $result[$index] = $item;
            }
        }

        return array_values($result);
    }

    /**
     * @throws \Throwable 当图片传输失败时
     */
    public function transferImageToLocal(string $remoteUrl): ?string
    {
        try {
            $response = $this->httpClient->request('GET', $remoteUrl);
            if (200 !== $response->getStatusCode()) {
                return null;
            }

            $content = $response->getContent(false);
            $tempPath = tempnam(sys_get_temp_dir(), 'silicon-img-');
            if (false === $tempPath) {
                return null;
            }

            file_put_contents($tempPath, $content);

            $uploadedFile = new UploadedFile(
                $tempPath,
                $this->resolveFilenameFromUrl($remoteUrl),
                null,
                null,
                true
            );

            $file = $this->fileService->uploadFile($uploadedFile, null);

            @unlink($tempPath);

            return $file->getPublicUrl() ?? $file->getUrl();
        } catch (\Throwable $exception) {
            $this->logger->warning('Mirror SiliconFlow image failed', [
                'remote_url' => $remoteUrl,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function resolveFilenameFromUrl(string $remoteUrl): string
    {
        $parsedPath = parse_url($remoteUrl, PHP_URL_PATH);
        $path = is_string($parsedPath) ? $parsedPath : '';
        $basename = trim(basename($path));

        if ('' === $basename) {
            return uniqid('silicon-image_', true) . '.jpg';
        }

        return urldecode($basename);
    }
}