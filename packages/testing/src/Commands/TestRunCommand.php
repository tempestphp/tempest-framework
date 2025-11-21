<?php

namespace Tempest\Testing\Commands;

use ReflectionException;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Testing\Events\TestFailed;
use Tempest\Testing\Events\TestSucceeded;
use Tempest\Testing\Exceptions\TestHasFailed;
use Tempest\Testing\Test;

final class TestRunCommand
{
    use HasConsole;

    public function __construct(
        private readonly Container $container,
    ) {}

    #[ConsoleCommand(middleware: [WithDiscoveredTestsMiddleware::class])]
    public function __invoke(array $tests): void {
        foreach ($tests as $testName) {
            try {
                $test = Test::fromName($testName);
            } catch (ReflectionException) {
                // Reflection Error, skipping, probably need to provide an error

                continue;
            }

            try {
                $this->container->invoke($test->handler);
                $this->output(new TestSucceeded($test->name));
            } catch (TestHasFailed $exception) {
                $this->output(TestFailed::fromException($test->name, $exception));
            }
        }
    }

    private function output(object $event): void
    {
        $this->writeln('[EVENT] ' . serialize($event));
    }
}