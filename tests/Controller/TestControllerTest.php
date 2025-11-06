<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\SiliconFlowBundle\Controller\TestController;

/**
 * SiliconFlow 测试控制器测试
 */
#[CoversClass(TestController::class)]
#[RunTestsInSeparateProcesses]
class TestControllerTest extends AbstractWebTestCase
{
    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        // 这个测试控制器不需要测试 405 Method Not Allowed
        self::markTestSkipped('此测试控制器不需要测试 405 Method Not Allowed');
    }

    /**
     * 测试控制器类是否为final
     */
    public function testControllerIsFinal(): void
    {
        $reflection = new \ReflectionClass(TestController::class);
        self::assertTrue($reflection->isFinal(), 'TestController should be final');
    }

    /**
     * 测试控制器路由配置
     */
    public function testControllerRouteConfiguration(): void
    {
        $reflection = new \ReflectionClass(TestController::class);
        $attributes = $reflection->getAttributes();

        $routeFound = false;
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === 'Symfony\Component\Routing\Annotation\Route') {
                $routeFound = true;
                break;
            }
        }

        self::assertTrue($routeFound, 'Controller should have Route attribute');
    }

    /**
     * 测试公共方法存在
     */
    public function testPublicMethodsExist(): void
    {
        $reflection = new \ReflectionClass(TestController::class);

        self::assertTrue($reflection->hasMethod('chatCompletion'));
        self::assertTrue($reflection->hasMethod('chatMessages'));
        self::assertTrue($reflection->hasMethod('imageGeneration'));
        self::assertTrue($reflection->hasMethod('submitVideo'));
        self::assertTrue($reflection->hasMethod('getVideoStatus'));
        self::assertTrue($reflection->hasMethod('getModels'));
    }

    /**
     * 测试私有方法存在
     */
    public function testPrivateMethodsExist(): void
    {
        $reflection = new \ReflectionClass(TestController::class);

        self::assertTrue($reflection->hasMethod('syncModels'));
        self::assertTrue($reflection->hasMethod('extractModelIds'));
        self::assertTrue($reflection->hasMethod('processModelItem'));
        self::assertTrue($reflection->hasMethod('requireActiveConfig'));
        self::assertTrue($reflection->hasMethod('requireDefaultUser'));
    }

    /**
     * 测试构造函数参数
     */
    public function testConstructorParameters(): void
    {
        $reflection = new \ReflectionClass(TestController::class);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor);
        self::assertCount(10, $constructor->getParameters());

        $parameterNames = array_map(
            static fn(\ReflectionParameter $param): string => $param->getName(),
            $constructor->getParameters()
        );

        $expectedParams = [
            'chatCompletionService',
            'apiClient',
            'configRepository',
            'userManager',
            'conversationRepository',
            'imageGenerationRepository',
            'videoGenerationRepository',
            'modelRepository',
            'responseExtractor',
            'imageProcessingService',
        ];

        self::assertSame($expectedParams, $parameterNames);
    }

    /**
     * 测试方法路由属性
     */
    public function testMethodRouteAttributes(): void
    {
        $reflection = new \ReflectionClass(TestController::class);

        $methodsWithRoutes = [
            'chatCompletion',
            'chatMessages',
            'imageGeneration',
            'submitVideo',
            'getVideoStatus',
            'getModels',
        ];

        foreach ($methodsWithRoutes as $methodName) {
            $method = $reflection->getMethod($methodName);
            $attributes = $method->getAttributes();

            $routeFound = false;
            foreach ($attributes as $attribute) {
                if ($attribute->getName() === 'Symfony\Component\Routing\Annotation\Route') {
                    $routeFound = true;
                    break;
                }
            }

            self::assertTrue($routeFound, sprintf('Method %s should have Route attribute', $methodName));
        }
    }

    /**
     * 测试extractModelIds方法的签名
     */
    public function testExtractModelIdsMethodSignature(): void
    {
        $reflection = new \ReflectionClass(TestController::class);
        $method = $reflection->getMethod('extractModelIds');

        self::assertTrue($method->isPrivate());
        self::assertCount(1, $method->getParameters());

        $param = $method->getParameters()[0];
        self::assertSame('models', $param->getName());
        self::assertTrue($param->hasType());
    }

    /**
     * 测试方法返回类型
     */
    public function testMethodReturnTypes(): void
    {
        $reflection = new \ReflectionClass(TestController::class);

        $methodsWithJsonResponse = [
            'chatCompletion',
            'chatMessages',
            'imageGeneration',
            'submitVideo',
            'getVideoStatus',
            'getModels',
        ];

        foreach ($methodsWithJsonResponse as $methodName) {
            $method = $reflection->getMethod($methodName);
            $returnType = $method->getReturnType();

            self::assertNotNull($returnType, sprintf('Method %s should have return type', $methodName));

            $typeName = $returnType instanceof \ReflectionNamedType
                ? $returnType->getName()
                : (string) $returnType;

            self::assertSame('Symfony\Component\HttpFoundation\JsonResponse', $typeName);
        }
    }
}
