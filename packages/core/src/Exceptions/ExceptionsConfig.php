<?php

namespace Tempest\Core\Exceptions;

final class ExceptionsConfig
{
    /**
     * @param array<class-string<ExceptionReporter>> $reporters
     * @param bool $logging Whether exception logging is enabled.
     */
    public function __construct(
        public bool $logging = true,
        public array $reporters = [],
    ) {}

    /**
     * Adds an exception reporter to the configuration.
     *
     * @param class-string<ExceptionReporter> $reporter
     */
    public function addReporter(string $reporter): void
    {
        if ($this->logging === false && $reporter === LoggingExceptionReporter::class) {
            return;
        }

        $this->reporters[] = $reporter;
    }

    /**
     * Replaces the list of exception reporters.
     *
     * @param array<class-string<ExceptionReporter>> $reporters
     */
    public function setReporters(array $reporters): void
    {
        $this->reporters = $reporters;
    }
}
