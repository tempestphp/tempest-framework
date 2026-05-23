<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use ErrorException;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tempest\Container\GenericContainer;
use Tempest\Core\ExceptionHandler;
use Tempest\Core\Exceptions\ExceptionProcessor;
use Tempest\Core\FrameworkKernel;
use Throwable;

final class FrameworkKernelExceptionHandlerTest extends TestCase
{
    private GenericContainer $container {
        get => $this->container ??= new GenericContainer();
    }

    private FrameworkKernel $kernel {
        get => $this->kernel ??= new FrameworkKernel(root: __DIR__, container: $this->container);
    }

    private int $previousErrorReporting;

    #[PreCondition]
    protected function configure(): void
    {
        $this->previousErrorReporting = error_reporting(E_ALL);
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        error_reporting($this->previousErrorReporting);
        putenv('ENVIRONMENT=testing');

        restore_error_handler();
        restore_exception_handler();

        GenericContainer::setInstance(null);
    }

    #[Test]
    public function warnings_notices_and_deprecations_are_processed_without_throwing(): void
    {
        putenv('ENVIRONMENT=local');

        $this->container->singleton(ExceptionHandler::class, new TestingExceptionHandler());
        $this->container->singleton(ExceptionProcessor::class, $processor = new TestingExceptionProcessor());

        $this->kernel->registerExceptionHandler();

        try {
            trigger_error('report warning', E_USER_WARNING);
            trigger_error('report deprecation', E_USER_DEPRECATED);
            trigger_error('report notice', E_USER_NOTICE);
        } catch (Throwable $throwable) {
            $this->fail(sprintf('Expected no exception to be thrown, but got %s: %s', $throwable::class, $throwable->getMessage()));
        }

        $this->assertCount(3, $processor->processed);

        /** @var ErrorException */
        $warning = $processor->processed[0];
        $this->assertInstanceOf(ErrorException::class, $warning);
        $this->assertSame(E_USER_WARNING, $warning->getSeverity());
        $this->assertSame('report warning', $warning->getMessage());

        /** @var ErrorException */
        $deprecation = $processor->processed[1];
        $this->assertInstanceOf(ErrorException::class, $deprecation);
        $this->assertSame(E_USER_DEPRECATED, $deprecation->getSeverity());
        $this->assertSame('report deprecation', $deprecation->getMessage());

        /** @var ErrorException */
        $notice = $processor->processed[2];
        $this->assertInstanceOf(ErrorException::class, $notice);
        $this->assertSame(E_USER_NOTICE, $notice->getSeverity());
        $this->assertSame('report notice', $notice->getMessage());
    }

    #[Test]
    public function suppressions_are_not_processed_or_thrown(): void
    {
        putenv('ENVIRONMENT=local');

        $this->container->singleton(ExceptionHandler::class, new TestingExceptionHandler());
        $this->container->singleton(ExceptionProcessor::class, $processor = new TestingExceptionProcessor());

        $this->kernel->registerExceptionHandler();

        @trigger_error('suppressed warning', E_USER_WARNING);
        @trigger_error('suppressed deprecation', E_USER_DEPRECATED);
        @trigger_error('suppressed notice', E_USER_NOTICE);

        $this->assertCount(0, $processor->processed);
    }

    #[Test]
    public function uncaught_exceptions_are_forwarded_to_the_registered_exception_handler(): void
    {
        putenv('ENVIRONMENT=local');

        $handler = new TestingExceptionHandler();

        $this->container->singleton(ExceptionHandler::class, $handler);
        $this->container->singleton(ExceptionProcessor::class, new TestingExceptionProcessor());

        $this->kernel->registerExceptionHandler();

        $kernelExceptionHandler = set_exception_handler(static function (Throwable $throwable): void {});

        if (! is_callable($kernelExceptionHandler)) {
            $this->fail('Expected the kernel to register an exception handler callback.');
        }

        $exception = new RuntimeException('uncaught');
        $kernelExceptionHandler($exception);

        restore_exception_handler();

        $this->assertCount(1, $handler->handled);
        $this->assertSame($exception, $handler->handled[0]);
    }
}

final class TestingExceptionProcessor implements ExceptionProcessor
{
    /** @var list<Throwable> */
    private(set) array $processed = [];

    public function process(Throwable $throwable): void
    {
        $this->processed[] = $throwable;
    }
}

final class TestingExceptionHandler implements ExceptionHandler
{
    /** @var list<Throwable> */
    private(set) array $handled = [];

    public function handle(Throwable $throwable): void
    {
        $this->handled[] = $throwable;
    }
}
