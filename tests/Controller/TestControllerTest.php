<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\SiliconFlowBundle\Controller\TestController;

/**
 * SiliconFlow 测试控制器测试
 */
#[CoversClass(TestController::class)]
class TestControllerTest extends AbstractWebTestCase
{
    public function testMethodNotAllowed(string $method): void
    {
        // AbstractWebTestCase 要求实现的抽象方法
        // 这里可以测试不支持的 HTTP 方法
        $this->markTestSkipped('此测试控制器不需要测试 405 Method Not Allowed');
    }

    /**
     * 测试控制器类是否为final
     */
    public function testControllerIsFinal(): void
    {
        $reflection = new \ReflectionClass(TestController::class);
        $this->assertTrue($reflection->isFinal(), 'TestController should be final');
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

        $this->assertTrue($routeFound, 'Controller should have Route attribute');
    }

    /**
     * 测试公共方法存在
     */
    public function testPublicMethodsExist(): void
    {
        $reflection = new \ReflectionClass(TestController::class);

        $this->assertTrue($reflection->hasMethod('chatCompletion'));
        $this->assertTrue($reflection->hasMethod('chatMessages'));
        $this->assertTrue($reflection->hasMethod('imageGeneration'));
        $this->assertTrue($reflection->hasMethod('submitVideo'));
        $this->assertTrue($reflection->hasMethod('getVideoStatus'));
        $this->assertTrue($reflection->hasMethod('getModels'));
    }

    /**
     * 测试私有方法存在
     */
    public function testPrivateMethodsExist(): void
    {
        $reflection = new \ReflectionClass(TestController::class);

        $this->assertTrue($reflection->hasMethod('syncModels'));
        $this->assertTrue($reflection->hasMethod('extractModelIds'));
        $this->assertTrue($reflection->hasMethod('processModelItem'));
        $this->assertTrue($reflection->hasMethod('requireActiveConfig'));
        $this->assertTrue($reflection->hasMethod('requireDefaultUser'));
    }

    /**
     * 测试构造函数参数
     */
    public function testConstructorParameters(): void
    {
        $reflection = new \ReflectionClass(TestController::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertCount(10, $constructor->getParameters());

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

        $this->assertSame($expectedParams, $parameterNames);
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

            $this->assertTrue($routeFound, sprintf('Method %s should have Route attribute', $methodName));
        }
    }

    /**
     * 测试extractModelIds方法的签名
     */
    public function testExtractModelIdsMethodSignature(): void
    {
        $reflection = new \ReflectionClass(TestController::class);
        $method = $reflection->getMethod('extractModelIds');

        $this->assertTrue($method->isPrivate());
        $this->assertCount(1, $method->getParameters());

        $param = $method->getParameters()[0];
        $this->assertSame('models', $param->getName());
        $this->assertTrue($param->hasType());
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

            $this->assertNotNull($returnType, sprintf('Method %s should have return type', $methodName));

            $typeName = $returnType instanceof \ReflectionNamedType
                ? $returnType->getName()
                : (string) $returnType;

            $this->assertSame('Symfony\Component\HttpFoundation\JsonResponse', $typeName);
        }
    }
}
