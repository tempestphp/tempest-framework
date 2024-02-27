<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Commands\Test\Test;
use Tempest\Console\ConsoleCommand;
use Tempest\Container\Container;

final readonly class ServeCommand
{
    public function __construct(private Container $container)
    {
    }

    #[ConsoleCommand(
        name: 'serve',
        description: 'Start a PHP development server'
    )]
    public function __invoke(string $host = 'localhost:8000', string $publicDir = 'public/'): void
    {
        var_dump($this->container->call(Test::class, '__construct'));

        passthru("php -S {$host} -t {$publicDir}");
    }
}
