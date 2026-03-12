<?php

namespace Tempest\Core\Exceptions;

use Closure;
use PHPUnit\Framework\Assert;
use Tempest\Container\Container;

final class ExceptionTester
{
    private(set) ?TestingExceptionProcessor $processor = null;

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * Allows exceptions to be processed, which means they will go through the reporting process.
     */
    public function allowProcessing(bool $allow = true): self
    {
        $this->recordExceptions();
        $this->processor->enabled = $allow;

        return $this;
    }

    /**
     * Prevents exceptions from being processed, which means they will not go through the reporting process. This is the default behavior.
     */
    public function preventProcessing(bool $prevent = true): self
    {
        return $this->allowProcessing(! $prevent);
    }

    /**
     * Asserts that the given `$exception` has been processed.
     *
     * @param null|Closure $callback A callback accepting the exception instance. The assertion fails if the callback returns `false`.
     * @param null|int $count If specified, the assertion fails if the exception has been processed a different amount of times.
     */
    public function assertProcessed(string|object $exception, ?Closure $callback = null, ?int $count = null): self
    {
        Assert::assertNotNull(
            actual: $reports = $this->findRecordedProcessings($exception),
            message: 'The exception was not processed.',
        );

        if ($count !== null) {
            Assert::assertCount($count, $reports, sprintf('Expected %s report(s), got %s.', $count, count($reports)));
        }

        if ($callback !== null) {
            foreach ($reports as $dispatch) {
                Assert::assertNotFalse($callback($dispatch), 'The callback failed.');
            }
        }

        return $this;
    }

    /**
     * Asserts that the given `$exception` was not processed.
     */
    public function assertNotProcessed(string|object $exception): self
    {
        Assert::assertEmpty(
            actual: $this->findRecordedProcessings($exception),
            message: 'The exception was processed.',
        );

        return $this;
    }

    /**
     * Asserts that no exceptions were processed.
     */
    public function assertNothingProcessed(): self
    {
        Assert::assertEmpty(
            actual: $this->processor->processed,
            message: sprintf('There are unexpected processed exceptions: [%s]', implode(', ', $this->processor->processed)),
        );

        return $this;
    }

    private function findRecordedProcessings(string|object $exception): array
    {
        return array_filter($this->processor->processed, function (string|object $reported) use ($exception) {
            if ($reported === $exception) {
                return true;
            }

            if (class_exists($exception) && is_a($reported, $exception, allow_string: true)) {
                return true;
            }

            return false;
        });
    }

    /**
     * Records exceptions being reported.
     */
    private function recordExceptions(): self
    {
        $this->container->unregister(ExceptionProcessor::class);
        $this->processor = new TestingExceptionProcessor(
            processor: $this->container->get(ExceptionProcessor::class),
            enabled: true,
        );

        $this->container->singleton(ExceptionProcessor::class, $this->processor);

        return $this;
    }
}
