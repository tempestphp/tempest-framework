<?php

declare(strict_types=1);

namespace Tempest\Vite;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Container\Container;

final readonly class ViteConfigCommand
{
    public function __construct(
        private Console $console,
        private Container $container,
    ) {}

    #[ConsoleCommand(name: 'vite:config', hidden: true)]
    public function __invoke(?string $tag = null): void
    {
        $config = $this->container->get(ViteConfig::class, $tag);

        $this->console->writeRaw(json_encode([
            'build_directory' => $config->buildDirectory,
            'bridge_file_name' => $config->bridgeFileName,
            'manifest' => $config->manifest,
            'entrypoints' => $config->entrypoints,
        ]));
    }
}
