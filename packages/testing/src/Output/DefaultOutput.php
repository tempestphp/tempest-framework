<?php

namespace Tempest\Testing\Output;

use Tempest\Console\HasConsole;
use Tempest\Testing\Events\TestFailed;
use Tempest\Testing\Events\TestFinished;
use Tempest\Testing\Events\TestRunEnded;
use Tempest\Testing\Events\TestRunStarted;
use Tempest\Testing\Events\TestsChunked;
use Tempest\Testing\Events\TestSkipped;
use Tempest\Testing\Events\TestStarted;
use Tempest\Testing\Events\TestSucceeded;
use Tempest\Testing\Runner\TestResult;

use function Tempest\Support\str;

final class DefaultOutput implements TestOutput
{
    use HasConsole;

    public function __construct(
        public bool $verbose = false,
        private TestResult $result = new TestResult(),
    ) {}

    public function onTestsChunked(TestsChunked $event): void
    {
        if ($this->verbose) {
            $this->writeln()
                ->info(sprintf(
                    'will run on %d %s',
                    $event->processCount,
                    str('process')->pluralize($event->processCount),
                ))
                ->writeln();
        }
    }

    public function onTestStarted(TestStarted $event): void
    {
        return;
    }

    public function onTestFailed(TestFailed $event): void
    {
        $this->result->addFailed();

        $this->error(sprintf('<style="fg-red">%s</style>', $event->name));
        $this->writeln(sprintf('  <style="fg-red dim">//</style> <style="fg-red underline">%s</style>', $event->location));
        $this->writeln(sprintf('  <style="fg-red dim">//</style> <style="fg-red">%s</style>', $event->reason));
        $this->writeln();
    }

    public function onTestSkipped(TestSkipped $event): void
    {
        $this->result->addSkipped();

        if ($this->verbose) {
            $this->info("skipped: {$event->name}");
        }
    }

    public function onTestSucceeded(TestSucceeded $event): void
    {
        $this->result->addSucceeded();

        if ($this->verbose) {
            $this->success($event->name);
        }
    }

    public function onTestFinished(TestFinished $event): void
    {
        return;
    }

    public function onTestRunStarted(TestRunStarted $event): void
    {
        $this->result->startTime();
    }

    public function onTestRunEnded(TestRunEnded $event): void
    {
        $this->result->endTime();

        $message = sprintf(
            '<style="bg-green"> %d succeeded </style> <style="bg-red"> %d failed </style> <style="bg-blue"> %d skipped </style> <style="bg-yellow"> %ss </style>',
            $this->result->succeeded,
            $this->result->failed,
            $this->result->skipped,
            $this->result->elapsedTime,
        );

        if ($this->result->failed > 0 || $this->verbose) {
            $this->writeln();
        }

        $this->writeln($message);
    }
}
