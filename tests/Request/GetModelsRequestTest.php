<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\SiliconFlowBundle\Entity\SiliconFlowConfig;
use Tourze\SiliconFlowBundle\Exception\ApiException;
use Tourze\SiliconFlowBundle\Request\GetModelsRequest;

#[CoversClass(GetModelsRequest::class)]
final class GetModelsRequestTest extends RequestTestCase
{
    public function testBuildRequestOptions(): void
    {
        $config = new SiliconFlowConfig();
        $config->setName('model');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('model-token');

        $request = new GetModelsRequest($config, 'text', ['page' => 2]);

        $options = $request->getRequestOptions();
        self::assertArrayHasKey('query', $options);
        self::assertArrayHasKey('headers', $options);

        $query = $options['query'];
        self::assertIsArray($query);
        self::assertSame('text', $query['type'] ?? null);
        self::assertSame(2, $query['page'] ?? null);

        $headers = $options['headers'];
        self::assertIsArray($headers);
        self::assertSame('Bearer model-token', $headers['Authorization'] ?? null);

        self::assertSame('GET', $request->getRequestMethod());
    }

    public function testParseResponseRequiresData(): void
    {
        $this->expectException(ApiException::class);

        $config = new SiliconFlowConfig();
        $config->setName('model');
        $config->setBaseUrl('https://api.siliconflow.cn');
        $config->setApiToken('model-token');

        $request = new GetModelsRequest($config);
        $request->parseResponse('{}');
    }
}
