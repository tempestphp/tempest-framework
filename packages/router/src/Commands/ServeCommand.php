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
    public function __invoke(string $host = '127.0.0.1', int $port = 8000, string $publicDir = 'public/'): void
    {
        $routerFile = __DIR__ . '/router.php';

        if (Str\contains($host, ':')) {
            [$host, $overriddenPort] = explode(':', $host, limit: 2);

            $host = $host ?: '127.0.0.1';

            $port = Number\parse($overriddenPort, default: $port);
        }

        passthru("php -S {$host}:{$port} -t {$publicDir} {$routerFile}");
    }
}
