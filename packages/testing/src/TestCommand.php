<?php

namespace Tempest\Testing;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\EventBus\EventBus;
use Tempest\Testing\Events\TestFailed;
use Tempest\Testing\Events\TestSkipped;
use Tempest\Testing\Events\TestSucceeded;

final class TestCommand
{
    use HasConsole;

    private bool $all = false;

    public function __construct(
        private readonly TestConfig $testConfig,
        private readonly Container $container,
        private readonly EventBus $eventBus,
    ) {}

    #[ConsoleCommand]
    public function __invoke(
        #[ConsoleArgument(description: 'Only run tests matching the given fuzzy filter')]
        ?string $filter = null,
        #[ConsoleArgument(description: 'Show all output, including succeeding tests')]
        bool $all = false,
    ): void {
        $this->eventBus->listen($this->onTestFailed(...));
        $this->eventBus->listen($this->onTestSucceeded(...));
        $this->eventBus->listen($this->onTestSkipped(...));

        $this->all = $all;

        $runner = new TestRunner('Default', $filter);

        $result = $runner->run(
            $this->container,
            $this->testConfig->tests,
        );

        if ($result->succeeded) {
            $this->success("{$result->succeeded} successful tests");
        }

        if ($result->failed) {
            $this->error("{$result->failed} failed tests");
        }

        if ($result->succeeded === 0 && $result->failed === 0) {
            $this->info("No tests were run");
        }
    }

    public function onTestFailed(TestFailed $event): void
    {
        $message = sprintf(
            <<<'TXT'
                    %s
                    %s
                    TXT,
            $event->exception->reason,
            $event->exception->location
        );

        $this->error($message, $event->name);
    }

    public function onTestSkipped(TestSkipped $event): void
    {
        if (! $this->all) {
            return;
        }

        $this->info('skipped', $event->name);
    }

    public function onTestSucceeded(TestSucceeded $event): void
    {
        if (! $this->all) {
            return;
        }

        $this->info('check', $event->name);
    }
}