<?php

declare(strict_types=1);

namespace Tempest\Http\Commands;

use Symfony\Component\Process\Process;
use Tempest\Console\ConsoleCommand;

final readonly class ServeCommand
{
    #[ConsoleCommand(
        name: 'serve',
        description: 'Start a PHP development server'
    )]
    public function __invoke(string $host = 'localhost', int $port = 8000, string $publicDir = 'public/'): void
    {
        $routerFile = __DIR__ . '/router.php';

        $process = new Process(
            command: ['php', '-S', "{$host}:{$port}", $routerFile],
            cwd: $publicDir,
        );

        $process->start(function ($type, $buffer): void {
            echo $buffer;
        });

        while ($process->isRunning()) {
            usleep(500 * 1000);
        }
    }
}
