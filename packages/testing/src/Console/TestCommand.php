<?php

namespace Tempest\Testing\Console;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\EventBus\EventBus;
use Tempest\Process\ProcessExecutor;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Testing\Actions\ChunkAndRunTests;
use Tempest\Testing\Events\TestFailed;
use Tempest\Testing\Events\TestRunEnded;
use Tempest\Testing\Events\TestRunStarted;
use Tempest\Testing\Events\TestsChunked;
use Tempest\Testing\Events\TestSkipped;
use Tempest\Testing\Events\TestSucceeded;
use Tempest\Testing\Test;
use Tempest\Testing\TestConfig;
use Tempest\Testing\TestResult;
use function Tempest\event;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class TestCommand
{
    use HasConsole;

    private bool $verbose = false;
    private TestResult $result;

    public function __construct(
        private readonly TestConfig $testConfig,
        private readonly Container $container,
        private readonly EventBus $eventBus,
        private readonly ProcessExecutor $executor,
    ) {}

    #[ConsoleCommand(middleware: [WithDiscoveredTestsMiddleware::class])]
    public function __invoke(
        #[ConsoleArgument(description: 'Only run tests matching this fuzzy filter')]
        ?string $filter = null,
        #[ConsoleArgument(description: 'Number of processes to run tests in parallel')]
        int $processes = 5,
        #[ConsoleArgument(description: 'Show all output, including succeeding and skipped tests', aliases: ['-v'])]
        bool $verbose = false,
    ): void
    {
        $this->verbose = $verbose;
        $this->result = new TestResult();

        $this->eventBus->listen($this->onTestFailed(...));
        $this->eventBus->listen($this->onTestSucceeded(...));
        $this->eventBus->listen($this->onTestSkipped(...));
        $this->eventBus->listen($this->onTestsChunked(...));
        $this->eventBus->listen($this->onTestRunStarted(...));
        $this->eventBus->listen($this->onTestRunEnded(...));

        new ChunkAndRunTests()(
            tests: $this->getTests($filter),
            processes: $processes
        );
    }

    public function onTestsChunked(TestsChunked $event): void
    {
        if ($this->verbose) {
            $this->info(sprintf(
                "Running on %d %s",
                $event->processCount,
                str('process')->pluralize($event->processCount),
            ))->writeln();
        }
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
    }

    public function onTestSucceeded(TestSucceeded $event): void
    {
        $this->result->addSucceeded();

        if ($this->verbose) {
            $this->success($event->name);
        }
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

    private function getTests(?string $filter): ImmutableArray
    {
        $tests = arr($this->testConfig->tests);

        if (! $filter) {
            return $tests;
        }

        return $tests->filter(function (Test $test) use ($filter) {
            if (! $test->matchesFilter($filter)) {
                event(new TestSkipped($test->name));
                return false;
            }

            return true;
        });
    }
}