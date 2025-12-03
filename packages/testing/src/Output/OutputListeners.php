<?php

namespace Tempest\Testing\Output;

use Tempest\Console\HasConsole;
use Tempest\Container\Singleton;
use Tempest\EventBus\EventHandler;
use Tempest\Testing\Events\TestFailed;
use Tempest\Testing\Events\TestFinished;
use Tempest\Testing\Events\TestRunEnded;
use Tempest\Testing\Events\TestRunStarted;
use Tempest\Testing\Events\TestsChunked;
use Tempest\Testing\Events\TestSkipped;
use Tempest\Testing\Events\TestStarted;
use Tempest\Testing\Events\TestSucceeded;

#[Singleton]
final class OutputListeners
{
    use HasConsole;

    public function __construct(
        private TestOutput $output,
    ) {}

    #[EventHandler]
    public function onTestsChunked(TestsChunked $event): void
    {
        $this->output->onTestsChunked($event);
    }

    #[EventHandler]
    public function onTestStarted(TestStarted $event): void
    {
        $this->output->onTestStarted($event);
    }

    #[EventHandler]
    public function onTestFailed(TestFailed $event): void
    {
        $this->output->onTestFailed($event);
    }

    #[EventHandler]
    public function onTestSkipped(TestSkipped $event): void
    {
        $this->output->onTestSkipped($event);
    }

    #[EventHandler]
    public function onTestSucceeded(TestSucceeded $event): void
    {
        $this->output->onTestSucceeded($event);
    }

    #[EventHandler]
    public function onTestFinished(TestFinished $event): void
    {
        $this->output->onTestFinished($event);
    }

    #[EventHandler]
    public function onTestRunStarted(TestRunStarted $event): void
    {
        $this->output->onTestRunStarted($event);
    }

    #[EventHandler]
    public function onTestRunEnded(TestRunEnded $event): void
    {
        $this->output->onTestRunEnded($event);
    }
}
