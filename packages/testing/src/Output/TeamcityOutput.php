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

final class TeamcityOutput implements TestOutput
{
    use HasConsole;

    public function __construct(
        public bool $verbose = false,
        private TestResult $result = new TestResult(),
    ) {}

    public function onTestsChunked(TestsChunked $event): void
    {
        return;
    }

    public function onTestStarted(TestStarted $event): void
    {
        $this->writeln($event->teamcityMessage);
    }

    public function onTestFailed(TestFailed $event): void
    {
        $this->writeln($event->teamcityMessage);
    }

    public function onTestSkipped(TestSkipped $event): void
    {
        $this->writeln($event->teamcityMessage);
    }

    public function onTestSucceeded(TestSucceeded $event): void
    {
        return;
    }

    public function onTestFinished(TestFinished $event): void
    {
        $this->writeln($event->teamcityMessage);
    }

    public function onTestRunStarted(TestRunStarted $event): void
    {
        $this->writeln($event->teamcityMessage);
    }

    public function onTestRunEnded(TestRunEnded $event): void
    {
        $this->writeln($event->teamcityMessage);
    }
}
