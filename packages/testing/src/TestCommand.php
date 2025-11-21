<?php

namespace Tempest\Testing;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\EventBus\EventBus;
use Tempest\Process\ProcessExecutor;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Testing\Events\TestFailed;
use Tempest\Testing\Events\TestSkipped;
use Tempest\Testing\Events\TestSucceeded;
use function Tempest\event;
use function Tempest\Support\arr;

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

    #[ConsoleCommand]
    public function __invoke(
        #[ConsoleArgument(description: 'Only run tests matching this fuzzy filter')]
        ?string $filter = null,
        #[ConsoleArgument(description: 'Number of processes to run tests in parallel')]
        int $processes = 5,
        #[ConsoleArgument(description: 'Show all output, including succeeding and skipped tests', aliases: ['-v'])]
        bool $verbose = false,
    ): void {
        $this->verbose = $verbose;
        $this->result = new TestResult();
        $this->eventBus->listen($this->onTestFailed(...));
        $this->eventBus->listen($this->onTestSucceeded(...));
        $this->eventBus->listen($this->onTestSkipped(...));

        $tests = $this->getTests($filter);

        $this->result->startTime();

        $chunks = ceil($tests->count() / $processes);

        $tests = $tests
            ->chunk($chunks)
            ->map(fn (ImmutableArray $tests, int $i) => new TestRunner($i)->run($tests));

        $this->info("Running on {$tests->count()} processes");

        $tests->map(fn (TestRunner $runner) => $runner->wait());

        $this->result->endTime();

        $this->renderResult();
    }

    public function onTestFailed(TestFailed $event): void
    {
        $message = sprintf(
            <<<'TXT'
                    %s
                    %s
                    TXT,
            $event->reason,
            $event->location
        );

        $this->result->addFailed();
        $this->error($message, $event->name);
    }

    public function onTestSkipped(TestSkipped $event): void
    {
        if (! $this->verbose) {
            return;
        }

        $this->result->addSkipped();
        $this->info('skipped', $event->name);
    }

    public function onTestSucceeded(TestSucceeded $event): void
    {
        if (! $this->verbose) {
            return;
        }

        $this->result->addSucceeded();
        $this->success('check', $event->name);
    }

    private function renderResult(): void
    {
        $this->info("Tests took {$this->result->elapsedTime} seconds to run");

        if ($this->result->skipped) {
            $this->info("{$this->result->skipped} skipped tests");
        }

        if ($this->result->succeeded) {
            $this->success("{$this->result->succeeded} successful tests");
        }

        if ($this->result->failed) {
            $this->error("{$this->result->failed} failed tests");
        }
    }

    private function getTests(?string $filter): ImmutableArray
    {
        $tests = $this->testConfig->tests;

        if ($filter) {
            foreach ($tests as $i => $test) {
                if (! $test->matchesFilter($filter)) {
                    unset($tests[$i]);
                    event(new TestSkipped($test->name));
                }
            }
        }

        return arr($tests);
    }
}