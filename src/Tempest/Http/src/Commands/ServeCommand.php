<?php

declare(strict_types=1);

namespace Tempest\Http\Commands;

use Tempest\Console\ConsoleCommand;

final readonly class ServeCommand
{
    #[ConsoleCommand(
        name: 'serve',
        description: 'Start a PHP development server',
    )]
    public function __invoke(string $host = 'localhost', int $port = 8000, string $publicDir = 'public/'): void
    {
        $routerFile = __DIR__ . '/router.php';
        passthru("php -S {$host}:{$port} -t {$publicDir} {$routerFile}");
    }
}
