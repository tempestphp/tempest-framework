<?php

namespace Tempest\Core;

use Closure;
use PHPUnit\Framework\Assert;

final readonly class ExceptionTester
{
    public function __construct(
        private ExceptionReporter $reporter,
    ) {}

    /**
     * Prevents the reporter from reporting exceptions.
     */
    public function preventReporting(bool $prevent = true): self
    {
        $this->reporter->enabled = ! $prevent;

        return $this;
    }

    /**
     * Asserts that the given `$exception` has been reported.
     *
     * @param null|Closure $callback A callback accepting the exception instance. The assertion fails if the callback returns `false`.
     * @param null|int $count If specified, the assertion fails if the exception has been reported a different amount of times.
     */
    public function assertReported(string|object $exception, ?Closure $callback = null, ?int $count = null): self
    {
        Assert::assertNotNull(
            actual: $reports = $this->findReports($exception),
            message: 'The exception was not reported.',
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
     * Asserts that the given `$exception` was not reported.
     */
    public function assertNotReported(string|object $exception): self
    {
        Assert::assertEmpty(
            actual: $this->findReports($exception),
            message: 'The exception was reported.',
        );

        return $this;
    }

    /**
     * Asserts that no exceptions were reported.
     */
    public function assertNothingReported(): self
    {
        Assert::assertEmpty(
            actual: $this->reporter->reported,
            message: sprintf('There are unexpected reported exceptions: [%s]', implode(', ', $this->reporter->reported)),
        );

        return $this;
    }

    private function findReports(string|object $exception): array
    {
        return array_filter($this->reporter->reported, function (string|object $reported) use ($exception) {
            if ($reported === $exception) {
                return true;
            }

            if (class_exists($exception) && is_a($reported, $exception, allow_string: true)) {
                return true;
            }

            return false;
        });
    }
}
