<?php

namespace Tempest\Testing;

use Tempest\Console\HasConsole;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Singleton;
use Tempest\EventBus\EventHandler;
use Tempest\Testing\Events\TestFailed;
use Tempest\Testing\Events\TestRunEnded;
use Tempest\Testing\Events\TestRunStarted;
use Tempest\Testing\Events\TestsChunked;
use Tempest\Testing\Events\TestSkipped;
use Tempest\Testing\Events\TestSucceeded;
use Tempest\Testing\Runner\TestResult;
use function Tempest\Support\str;

#[Singleton]
final class TestEventListeners
{
    use HasConsole;

    private TestResult $result;

    public function __construct(
        private readonly ConsoleArgumentBag $argumentBag,
    ) {
        $this->result = new TestResult();
    }

    private bool $isVerbose {
        get => $this->argumentBag->has('verbose', '-v');
    }

    #[EventHandler]
    public function onTestsChunked(TestsChunked $event): void
    {
        if ($this->isVerbose) {
            $this->info(sprintf(
                "Running on %d %s",
                $event->processCount,
                str('process')->pluralize($event->processCount),
            ))->writeln();
        }
    }

    #[EventHandler]
    public function onTestFailed(TestFailed $event): void
    {
        $this->result->addFailed();

        $this->error(sprintf('<style="fg-red">%s</style>', $event->name));
        $this->writeln(sprintf('  <style="fg-red dim">//</style> <style="fg-red underline">%s</style>', $event->location));
        $this->writeln(sprintf('  <style="fg-red dim">//</style> <style="fg-red">%s</style>', $event->reason));
        $this->writeln();
    }

    #[EventHandler]
    public function onTestSkipped(TestSkipped $event): void
    {
        $this->result->addSkipped();
    }

    #[EventHandler]
    public function onTestSucceeded(TestSucceeded $event): void
    {
        $this->result->addSucceeded();

        if ($this->isVerbose) {
            $this->success($event->name);
        }
    }

    #[EventHandler]
    public function onTestRunStarted(TestRunStarted $event): void
    {
        $this->result->startTime();
    }

    #[EventHandler]
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

        if ($this->result->failed > 0 || $this->isVerbose) {
            $this->writeln();
        }

        $this->writeln($message);
    }
}