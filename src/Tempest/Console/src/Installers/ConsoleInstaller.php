<?php

declare(strict_types=1);

namespace Tempest\Console\Installers;

use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use function Tempest\root_path;
use function Tempest\Support\str;

final class ConsoleInstaller implements Installer
{
    use PublishesFiles;

    public function getName(): string
    {
        return 'console';
    }

    public function install(): void
    {
        $this->installMainNamespace();

        $this->publish(
            source: __DIR__ . '/tempest',
            destination: root_path('tempest'),
            callback: function (string $source, string $destination): void {
                if (PHP_OS_FAMILY !== 'Windows') {
                    /** @phpstan-ignore-next-line */
                    exec("chmod +x {$destination}");
                }
            },
        );
    }

    private function installMainNamespace(): void
    {
        if ($this->composer->mainNamespace !== null) {
            return;
        }

        if (! $this->confirm('Tempest detected no main project namespace. Do you want to create it?', default: true)) {
            return;
        }

        $appPath = root_path($this->ask('Which path do you wish to use as your main project directory?', default: 'app/'));

        $defaultAppNamespace = str($appPath)
            ->replaceStart(root_path(), '')
            ->trim('/')
            ->explode('/')
            ->map(fn (string $part) => ucfirst($part))
            ->implode('\\')
            ->append('\\')
            ->toString();

        $appNamespace = str($this->ask('Which namespace do you wish to use?', default: $defaultAppNamespace))
            ->trim('\\')
            ->append('\\')
            ->toString();

        if (! is_dir($appPath)) {
            mkdir($appPath, recursive: true);
        }

        $this->composer
            ->addNamespace(
                $appNamespace,
                $appPath,
            )
            ->save();

        $this->success("Project namespace created: {$appPath}");
    }
}
