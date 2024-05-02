<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use ReflectionMethod;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutput;
use Tempest\Container\Container;

final class SchedulerRunInvocation
{
    public const string NAME = 'scheduler:invoke';

    public function __construct(
        private Container $container,
        private ConsoleOutput $output,
    ) {
    }

    #[ConsoleCommand(self::NAME)]
    public function __invoke(string $invocation): void
    {
        $this->output->info("Invoking $invocation");

        [$class, $method] = explode('::', $invocation);

        $reflectionMethod = new ReflectionMethod($class, $method);

        $reflectionMethod->invoke(
            $this->container->get($class),
        );
    }
}
