<?php

namespace Tempest\Framework\Installers;

use Tempest\Console\HasConsole;
use Tempest\Core\Installer;
use Tempest\Core\Kernel\LoadConfig;
use Tempest\Core\PublishesFiles;
use Tempest\Support\Str\ImmutableString;

use function Tempest\src_path;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class ConfigInstaller
{
    use HasConsole;
    use PublishesFiles;

    public function __construct(
        private readonly LoadConfig $loadConfig,
    ) {}

    #[Installer('Config', alias: 'config')]
    public function install(): void
    {
        $searchOptions = arr($this->loadConfig->find())
            ->map(fn (string $path) => str($path))
            ->filter(fn (ImmutableString $path) => $path->contains(['/packages/', '/vendor/']))
            ->mapWithKeys(fn (ImmutableString $path) => yield $path->toString() => $path->afterLast('/')->toString());

        if ($searchOptions->isEmpty()) {
            $this->error('No installable config files found.');
            return;
        }

        $selected = $this->ask(
            question: 'Select which config files you want to install',
            options: $searchOptions->keys(),
            multiple: true,
        );

        foreach ($selected as $selectedItem) {
            $newPath = $searchOptions[$selectedItem] ?? null;

            if (! $newPath) {
                continue;
            }

            $this->publish(
                $selectedItem,
                src_path('Config', $newPath),
            );
        }
    }
}
