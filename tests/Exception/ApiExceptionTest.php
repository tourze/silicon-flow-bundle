<?php

declare(strict_types=1);

namespace Tourze\SiliconFlowBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\SiliconFlowBundle\Exception\ApiException;

/**
 * SiliconFlow API 异常测试
 */
#[CoversClass(ApiException::class)]
class ApiExceptionTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 异常测试不需要额外的setup
    }

    /**
     * 测试异常实例化
     */
    public function testInstantiation(): void
    {
        $exception = new ApiException();
        self::assertInstanceOf(ApiException::class, $exception);
        self::assertInstanceOf(\RuntimeException::class, $exception);
    }

    /**
     * 测试异常类是否为final
     */
    public function testExceptionIsFinal(): void
    {
        $reflection = new \ReflectionClass(ApiException::class);
        self::assertTrue($reflection->isFinal(), 'ApiException should be final');
    }

    /**
     * 测试异常继承关系
     */
    public function testExceptionInheritance(): void
    {
        $exception = new ApiException();
        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertInstanceOf(\Exception::class, $exception);
        self::assertInstanceOf(\Throwable::class, $exception);
    }

    /**
     * 测试带消息的异常
     */
    public function testExceptionWithMessage(): void
    {
        $message = 'SiliconFlow API 调用失败';
        $exception = new ApiException($message);

        self::assertSame($message, $exception->getMessage());
    }

    /**
     * 测试带消息和错误码的异常
     */
    public function testExceptionWithMessageAndCode(): void
    {
        $message = 'API 请求超时';
        $code = 408;
        $exception = new ApiException($message, $code);

        self::assertSame($message, $exception->getMessage());
        self::assertSame($code, $exception->getCode());
    }

    /**
     * 测试带前一个异常的异常
     */
    public function testExceptionWithPreviousException(): void
    {
        $previousMessage = '网络连接失败';
        $previous = new \RuntimeException($previousMessage);

        $message = 'SiliconFlow API 不可用';
        $exception = new ApiException($message, 0, $previous);

        self::assertSame($message, $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
        self::assertSame($previousMessage, $exception->getPrevious()->getMessage());
    }

    /**
     * 测试空消息的异常
     */
    public function testExceptionWithEmptyMessage(): void
    {
        $exception = new ApiException('');
        self::assertSame('', $exception->getMessage());
    }

    /**
     * 测试异常抛出和捕获
     */
    public function testExceptionThrowAndCatch(): void
    {
        $message = '测试异常抛出';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage($message);

        throw new ApiException($message);
    }

    /**
     * 测试异常可以作为RuntimeException捕获
     */
    public function testExceptionCatchAsRuntimeException(): void
    {
        $message = '可作为RuntimeException捕获';

        try {
            throw new ApiException($message);
        } catch (\RuntimeException $e) {
            self::assertInstanceOf(ApiException::class, $e);
            self::assertSame($message, $e->getMessage());
        }
    }

    /**
     * 测试异常可以作为Exception捕获
     */
    public function testExceptionCatchAsException(): void
    {
        $message = '可作为Exception捕获';

        try {
            throw new ApiException($message);
        } catch (\Exception $e) {
            self::assertInstanceOf(ApiException::class, $e);
            self::assertSame($message, $e->getMessage());
        }
    }

    /**
     * 测试异常的字符串表示
     */
    public function testExceptionStringRepresentation(): void
    {
        $message = 'API 调用异常';
        $code = 500;
        $exception = new ApiException($message, $code);

        $string = (string) $exception;

        self::assertStringContainsString('ApiException', $string);
        self::assertStringContainsString($message, $string);
        self::assertStringContainsString((string) $code, $string);
    }

    /**
     * 测试异常的调用栈信息
     */
    public function testExceptionTraceInfo(): void
    {
        $exception = new ApiException('测试调用栈');

        $trace = $exception->getTrace();
        self::assertIsArray($trace);

        $file = $exception->getFile();
        self::assertIsString($file);
        self::assertStringEndsWith('ApiExceptionTest.php', $file);

        $line = $exception->getLine();
        self::assertIsInt($line);
        self::assertGreaterThan(0, $line);
    }
}