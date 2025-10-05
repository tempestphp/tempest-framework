<?php

declare(strict_types=1);

namespace Tempest\Router\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Intl\Number;
use Tempest\Support\Str;
use function Tempest\Support\path;

final readonly class ServeCommand
{
    use HasConsole;

    #[ConsoleCommand(
        name: 'serve',
        description: 'Starts a PHP development server',
    )]
    public function __invoke(
        string $host = 'localhost',
        int $port = 8000,
        int $httpsPort = 4433,
        string $publicDir = './public/',
        bool $worker = false,
    ): void {
        if (Str\contains($host, ':')) {
            [$host, $overriddenPort] = explode(':', $host, limit: 2);

            $host = $host ?: '127.0.0.1';

            $port = Number\parse($overriddenPort, default: $port);
        }

        if ($worker) {
            $this->worker($host, $port, $httpsPort, $publicDir);
        } else {
            $this->serve($host, $port, $publicDir);
        }
    }

    private function worker(string $host, int $port, int $httpsPort, string $publicDir): void
    {
        $this->success('Listening on http://' . $host . ':' . $port . ', https://' . $host . ':' . $httpsPort);

        $command = sprintf(<<<'SH'
        docker run \
            -e FRANKENPHP_CONFIG="worker %s" \
            -v $PWD:/app \
            -p %d:80 -p %d:443 -p %d:443/udp \
            tempest
        SH,
        path($publicDir, 'index.php')->toString(),
        $port,
        $httpsPort,
        $httpsPort
        );

        $this->info($command);

        passthru($command);
    }

    private function serve(string $host, int $port, string $publicDir): void
    {
        $routerFile = __DIR__ . '/router.php';

        passthru("php -S {$host}:{$port} -t {$publicDir} {$routerFile}");
    }
}
