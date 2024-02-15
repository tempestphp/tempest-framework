<?php

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleCommand;

final readonly class ServeCommand
{
    #[ConsoleCommand(
        name: 'serve',
        description: 'Start a PHP development server'
    )]
    public function __invoke(string $host = 'localhost:8000'): void
    {
        passthru("php -S {$host} -t public/");
    }
}