<?php

namespace Tempest\Testing;

use ReflectionException;
use ReflectionMethod;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Reflection\MethodReflector;
use Tempest\Testing\Events\TestFailed;
use Tempest\Testing\Events\TestSkipped;
use Tempest\Testing\Events\TestSucceeded;
use Tempest\Testing\Exceptions\TestHasFailed;

final class TestRunCommand
{
    use HasConsole;

    public function __construct(
        private readonly Container $container,
    ) {}

    #[ConsoleCommand]
    public function __invoke(array $tests): void {
        foreach ($tests as $testName) {
            try {
                $test = new MethodReflector(new ReflectionMethod(...explode('::', $testName)));
            } catch (ReflectionException) {
                // Reflection Error, skipping, probably need to provide an error

                continue;
            }

            try {
                $this->container->invoke($test);
                $this->output(new TestSucceeded($testName));
            } catch (TestHasFailed $exception) {
                $this->output(TestFailed::fromException($testName, $exception));
            }
        }
    }

    private function output(object $event): void
    {
        $this->writeln(serialize($event));
    }
}