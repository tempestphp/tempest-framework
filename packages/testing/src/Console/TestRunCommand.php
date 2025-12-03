<?php

namespace Tempest\Testing\Console;

use ReflectionException;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\EventBus\EventBusConfig;
use Tempest\Testing\Actions\RunTest;
use Tempest\Testing\Events\DispatchToParentProcessMiddleware;
use Tempest\Testing\Test;

final class TestRunCommand
{
    use HasConsole;

    public function __construct(
        private readonly RunTest $runTest,
        private readonly EventBusConfig $eventBusConfig,
    ) {}

    #[ConsoleCommand(
        middleware: [WithDiscoveredTestsMiddleware::class],
        hidden: true,
    )]
    public function __invoke(array $tests): void
    {
        $this->eventBusConfig->middleware->add(DispatchToParentProcessMiddleware::class);

        foreach ($tests as $testName) {
            try {
                $test = Test::fromName($testName);
            } catch (ReflectionException) {
                // Reflection Error, skipping, probably need to provide an error

                continue;
            }

            ($this->runTest)($test);
        }
    }
}
