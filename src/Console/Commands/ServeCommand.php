<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\Parameter;
use Tempest\Validation\Rules\Url;

final readonly class ServeCommand
{
    #[ConsoleCommand(
        name: 'serve',
        description: 'Start a PHP development server',
        help: [
            'The serve command starts a PHP development server.',
            'By default, the server will be available at http://localhost:8000.',
            'You can customize the host and port by passing the --host option.',
            'You can also customize the public directory by passing the --public-dir option.',
        ]
    )]
    public function __invoke(
        #[Parameter(help: 'The host and port to serve the application on.')]
        #[Url]
        string $host = 'localhost:8000',
        #[Url]
        #[Parameter(help: 'The public directory to serve the application from.')]
        string $publicDir = 'public/'
    ): void {
        passthru("php -S {$host} -t {$publicDir}");
    }
}
