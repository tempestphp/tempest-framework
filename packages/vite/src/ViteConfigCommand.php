<?php

declare(strict_types=1);

namespace Tempest\Vite;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Support\Json;

final readonly class ViteConfigCommand
{
    public function __construct(
        private Console $console,
        private ViteConfig $viteConfig,
    ) {}

    #[ConsoleCommand(name: 'vite:config', hidden: true)]
    public function __invoke(): void
    {
        $this->console->writeRaw(Json\encode([
            'build_directory' => $this->viteConfig->buildDirectory,
            'bridge_file_name' => $this->viteConfig->bridgeFileName,
            'manifest' => $this->viteConfig->manifest,
            'entrypoints' => $this->viteConfig->entrypoints,
        ]));
    }
}
