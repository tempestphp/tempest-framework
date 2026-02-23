<?php

declare(strict_types=1);

namespace Tempest\Router\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Intl\Number;
use Tempest\Support\Str;
use Tempest\Support\Str\ImmutableString;

use function Tempest\root_path;

if (class_exists(\Tempest\Console\ConsoleCommand::class)) {
    final readonly class ServeCommand
    {
        #[ConsoleCommand(
            name: 'serve',
            description: 'Starts a PHP development server',
        )]
        public function __invoke(
            string $host = '127.0.0.1',
            int $port = 8000,
            string $publicDir = 'public/',
            #[ConsoleArgument(
                description: 'Run via Aloft (Docker) instead of the built-in PHP dev server',
                aliases: ['--aloft'],
            )]
            bool $aloft = false,
        ): void {
            $resolvedHost = new ImmutableString($host);
            $resolvedPort = $port;
            $resolvedPublicDir = new ImmutableString($publicDir);

            if ($resolvedHost->contains(':')) {
                [$rawHost, $overriddenPort] = explode(':', $resolvedHost->toString(), limit: 2);

                $resolvedHost = new ImmutableString($rawHost ?: '127.0.0.1');
                $resolvedPort = (int) Number\parse($overriddenPort, default: $port);
            }

            if ($aloft) {
                $this->serveAloft($resolvedHost, $resolvedPort, $resolvedPublicDir);
            } else {
                $this->serveBuiltin($resolvedHost, $resolvedPort, $resolvedPublicDir);
            }
        }

        private function serveBuiltin(ImmutableString $host, int $port, ImmutableString $publicDir): void
        {
            $routerFile = new ImmutableString(__DIR__ . '/router.php');

            passthru("php -S {$host}:{$port} -t {$publicDir} {$routerFile}");
        }

        private function serveAloft(ImmutableString $host, int $port, ImmutableString $publicDir): void
        {
            passthru(
                "docker run --rm -it -p 80:8000 -p 443:8443 -p 443:8443/udp \
                -v "
                . root_path()
                . ":/app \
                -v "
                . root_path('.tempest/aloft/data')
                . ":/data \
                -v "
                . root_path('.tempest/aloft/config')
                . ":/config \
                tempestphp/aloft:latest-nonroot",
            );
        }
    }
}
