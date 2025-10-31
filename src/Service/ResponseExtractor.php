<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Service;

use Tourze\SiliconFlowBundle\Entity\SiliconFlowModel;

/**
 * 提取API响应数据的服务类
 *
 * @phpstan-type ImageUrl array{url: string}|null
 * @phpstan-type VideoUrl array{url: string}|null
 * @phpstan-type ContentBlock array{type: string, text: string}|null
 * @phpstan-type Timings array{inference: float|int}|null
 * @phpstan-type Results array{videos: VideoUrl[], timings: Timings, seed: int}|null
 */
final class ResponseExtractor
{
    /**
     * @param array<string, mixed> $response
     */
    public function extractAnswerFromMessagesResponse(array $response): string
    {
        $contentBlocks = $response['content'] ?? [];
        if (!is_array($contentBlocks)) {
            return '';
        }

        foreach ($contentBlocks as $block) {
            if (is_array($block) && ('text' === ($block['type'] ?? null))) {
                /** @var string $text */
                $text = $block['text'] ?? '';
                return $text;
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $response
     * @return string[]|null
     */
    public function extractImageUrls(array $response): ?array
    {
        if (!isset($response['images']) || !is_array($response['images'])) {
            return null;
        }

        $urls = [];
        foreach ($response['images'] as $image) {
            if (!is_array($image)) {
                continue;
            }
            if (isset($image['url']) && is_string($image['url']) && '' !== $image['url']) {
                $urls[] = $image['url'];
            }
        }

        return [] !== $urls ? $urls : null;
    }

    /**
     * @param array<string, mixed> $response
     * @return string[]|null
     */
    public function extractVideoUrls(array $response): ?array
    {
        $results = $response['results'] ?? [];
        if (!is_array($results)) {
            return null;
        }

        $videos = $results['videos'] ?? null;
        if (!is_array($videos)) {
            return null;
        }

        $urls = [];
        foreach ($videos as $video) {
            if (!is_array($video)) {
                continue;
            }
            if (isset($video['url']) && is_string($video['url']) && '' !== $video['url']) {
                $urls[] = $video['url'];
            }
        }

        return [] !== $urls ? $urls : null;
    }

    /**
     * @param string[]|null $urls
     */
    public function stringifyUrls(?array $urls): ?string
    {
        if (null === $urls || [] === $urls) {
            return null;
        }

        return implode(',', array_map(static fn (string $url): string => $url, $urls));
    }

    /**
     * @param array<string, mixed> $response
     */
    public function extractStatus(array $response, ?string $default): ?string
    {
        if (!isset($response['status'])) {
            return $default;
        }

        $status = $response['status'];
        return is_string($status) ? $status : $default;
    }

    /**
     * @param array<string, mixed> $response
     */
    public function extractStatusReason(array $response): ?string
    {
        if (!isset($response['reason'])) {
            return null;
        }

        $reason = $response['reason'];
        return is_string($reason) ? $reason : null;
    }

    /**
     * @param array<string, mixed> $response
     */
    public function extractInferenceTime(array $response): ?float
    {
        $results = $response['results'] ?? [];
        if (!is_array($results)) {
            return null;
        }

        $timings = $results['timings'] ?? [];
        if (!is_array($timings)) {
            return null;
        }

        if (!isset($timings['inference'])) {
            return null;
        }

        $inference = $timings['inference'];
        return is_numeric($inference) ? (float) $inference : null;
    }

    /**
     * @param array<string, mixed> $response
     */
    public function extractResultSeed(array $response): ?int
    {
        $results = $response['results'] ?? [];
        if (!is_array($results)) {
            return null;
        }

        if (!isset($results['seed'])) {
            return null;
        }

        $seed = $results['seed'];
        return is_numeric($seed) ? (int) $seed : null;
    }
}