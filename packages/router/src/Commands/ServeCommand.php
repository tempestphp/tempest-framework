<?php

declare(strict_types=1);

namespace Tempest\Router\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Intl\Number;
use Tempest\Support\Str;

final readonly class ServeCommand
{
    #[ConsoleCommand(
        name: 'serve',
        description: 'Starts a PHP development server',
    )]
    public function __invoke(string $host = 'localhost', int $port = 8000, string $publicDir = 'public/'): void
    {
        $routerFile = __DIR__ . '/router.php';

        if (Str\contains($host, ':')) {
            [$host, $overridenPort] = explode(':', $host, limit: 2);
            $port = Number\parse($overridenPort, default: $port);
        }

        passthru("php -S {$host}:{$port} -t {$publicDir} {$routerFile}");
    }
}
