<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use ReflectionMethod;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Container\Container;

final class SchedulerRunInvocationCommand
{
    public const string NAME = 'scheduler:invoke';

    public function __construct(
        private Container $container,
        private Console $console,
    ) {
    }

    #[ConsoleCommand(self::NAME)]
    public function __invoke(string $invocation): void
    {
        $this->console->info("Invoking $invocation");

        [$class, $method] = explode('::', $invocation);

        $reflectionMethod = new ReflectionMethod($class, $method);

        $reflectionMethod->invoke(
            $this->container->get($class),
        );
    }
}
