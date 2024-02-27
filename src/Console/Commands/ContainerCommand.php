<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Commands\Test\Test;
use Tempest\Console\ConsoleCommand;
use Tempest\Container\Container;

final readonly class ContainerCommand
{
    public function __construct(private Container $container)
    {
    }

    #[ConsoleCommand(
        name: 'container',
        description: 'Test only for container stuff'
    )]
    public function __invoke(): void
    {
        var_dump(
            $this->container->call(Test::class, '__construct')
        );
    }
}
