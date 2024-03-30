<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Validation\Rules\Url;

final readonly class ServeCommand
{
    #[ConsoleCommand(
        name: 'serve',
        description: 'Start a PHP development server'
    )]
    public function __invoke(
        #[Url]
        string $host = 'localhost:8000',
        string $publicDir = 'public/'
    ): void {
        passthru("php -S {$host} -t {$publicDir}");
    }
}
