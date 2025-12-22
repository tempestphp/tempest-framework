<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core\Exceptions;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\Exceptions\LoggingExceptionReporter;
use Tempest\Core\ProvidesContext;
use Tempest\Log\Logger;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class LoggingExceptionReporterTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function logs_exception_with_message(): void
    {
        $logger = new TestLogger();
        $reporter = new LoggingExceptionReporter($logger);
        $reporter->report(new Exception('Something went wrong'));

        $this->assertCount(2, $logger->logs);
        $this->assertSame('error', $logger->logs[0]['level']);
        $this->assertSame('Something went wrong', $logger->logs[0]['message']);
    }

    #[Test]
    public function logs_exception_with_fallback_message(): void
    {
        $logger = new TestLogger();
        $reporter = new LoggingExceptionReporter($logger);
        $reporter->report(new Exception(''));

        $this->assertCount(2, $logger->logs);
        $this->assertSame('error', $logger->logs[0]['level']);
        $this->assertSame('(no message)', $logger->logs[0]['message']);
    }

    #[Test]
    public function logs_exception_trace_separately(): void
    {
        $logger = new TestLogger();
        $reporter = new LoggingExceptionReporter($logger);
        $reporter->report(new Exception('Test'));

        $this->assertCount(2, $logger->logs);
        $this->assertSame('error', $logger->logs[1]['level']);
        $this->assertIsString($logger->logs[1]['message']);
        $this->assertStringStartsWith('#0 ', $logger->logs[1]['message']);
    }

    #[Test]
    public function logs_exception_with_context_when_exception_provides_context(): void
    {
        $logger = new TestLogger();
        $reporter = new LoggingExceptionReporter($logger);
        $reporter->report(new ExceptionWithContext('Test'));

        $this->assertCount(2, $logger->logs);
        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $logger->logs[0]['context']);
    }

    #[Test]
    public function logs_exception_with_empty_context_when_exception_does_not_provide_context(): void
    {
        $logger = new TestLogger();
        $reporter = new LoggingExceptionReporter($logger);
        $reporter->report(new Exception('Test'));

        $this->assertCount(2, $logger->logs);
        $this->assertEmpty($logger->logs[0]['context']);
    }
}

final class TestLogger implements Logger
{
    public array $logs = [];

    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'emergency', 'message' => $message, 'context' => $context];
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'alert', 'message' => $message, 'context' => $context];
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'critical', 'message' => $message, 'context' => $context];
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'error', 'message' => $message, 'context' => $context];
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'warning', 'message' => $message, 'context' => $context];
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'notice', 'message' => $message, 'context' => $context];
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'info', 'message' => $message, 'context' => $context];
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'debug', 'message' => $message, 'context' => $context];
    }

    public function log(mixed $level, string|\Stringable $message, array $context = []): void
    {
        $this->logs[] = ['level' => $level, 'message' => $message, 'context' => $context];
    }
}

final class ExceptionWithContext extends Exception implements ProvidesContext
{
    public function context(): iterable
    {
        return ['foo' => 'bar', 'baz' => 'qux'];
    }
}
