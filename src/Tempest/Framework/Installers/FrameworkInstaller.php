<?php

declare(strict_types=1);

namespace Tempest\Framework\Installers;

use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use function Tempest\root_path;

final class FrameworkInstaller implements Installer
{
    use PublishesFiles;

    public function getName(): string
    {
        return 'framework';
    }

    public function install(): void
    {
        $this->installMainNamespace();

        $this->publish(
            source: __DIR__ . '/../../../../.env.example',
            destination: root_path('.env.example'),
        );

        $this->publish(
            source: __DIR__ . '/../../../../.env.example',
            destination: root_path('.env'),
        );

        $this->publish(
            source: __DIR__ . '/index.php',
            destination: root_path('public/index.php'),
        );

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

        $appPath = root_path('app/');

        if (! is_dir($appPath)) {
            mkdir($appPath);
        }

        $this->composer
            ->addNamespace(
                'App\\',
                $appPath,
            )
            ->save();

        $this->success("Project namespace created: {$appPath}");
    }
}
