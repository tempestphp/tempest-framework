<?php

namespace Tempest\Testing\Output;

use Tempest\Testing\Events\TestFailed;
use Tempest\Testing\Events\TestFinished;
use Tempest\Testing\Events\TestRunEnded;
use Tempest\Testing\Events\TestRunStarted;
use Tempest\Testing\Events\TestsChunked;
use Tempest\Testing\Events\TestSkipped;
use Tempest\Testing\Events\TestStarted;
use Tempest\Testing\Events\TestSucceeded;

interface TestOutput
{
    public bool $verbose {
        set;
    }

    public function onTestsChunked(TestsChunked $event): void;

    public function onTestStarted(TestStarted $event): void;

    public function onTestFailed(TestFailed $event): void;

    public function onTestSkipped(TestSkipped $event): void;

    public function onTestSucceeded(TestSucceeded $event): void;

    public function onTestFinished(TestFinished $event): void;

    public function onTestRunStarted(TestRunStarted $event): void;

    public function onTestRunEnded(TestRunEnded $event): void;
}
