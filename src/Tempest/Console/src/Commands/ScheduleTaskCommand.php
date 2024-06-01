<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use ReflectionMethod;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Container\Container;
use Throwable;

final readonly class ScheduleTaskCommand
{
    public const string NAME = 'schedule:task';

    public function __construct(
        private Container $container,
        private Console $console,
    ) {
    }

    #[ConsoleCommand(
        name: self::NAME,
    )]
    public function __invoke(string $task): void
    {
        $console = $this->console->withLabel($task);

        $console->writeln('Starting');

        $parts = explode('::', $task);

        if (count($parts) !== 2) {
            $console->error("Invalid task");

            return;
        }

        $class = $parts[0];
        $method = $parts[1];

        try {
            $reflectionMethod = new ReflectionMethod($class, $method);
        } catch (Throwable $e) {
            $console->error($e->getMessage());

            return;
        }

        $reflectionMethod->invoke(
            $this->container->get($class, console: $console),
        );

        $console->success('Done');
    }
}
