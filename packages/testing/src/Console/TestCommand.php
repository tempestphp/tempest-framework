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
use Tempest\Testing\Config\TestConfig;
use Tempest\Testing\Events\TestSkipped;
use Tempest\Testing\Runner\TestResult;
use Tempest\Testing\Test;
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
        new ChunkAndRunTests()(
            tests: $this->getTests($filter),
            processes: $processes,
        );
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