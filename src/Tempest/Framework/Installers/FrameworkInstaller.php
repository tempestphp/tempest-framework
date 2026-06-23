<?php

declare(strict_types=1);

namespace Tempest\Framework\Installers;

use Tempest\Core\Installer;
use Tempest\Core\IsComponentInstaller;

use function Tempest\root_path;

final class FrameworkInstaller
{
    use IsComponentInstaller;

    #[Installer('Framework', alias: 'framework')]
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

        $tempest = $this->publish(
            source: __DIR__ . '/tempest',
            destination: root_path('tempest'),
            callback: function (string $_, string $destination): void {
                if (PHP_OS_FAMILY !== 'Windows') {
                    /** @phpstan-ignore-next-line */
                    exec("chmod +x {$destination}");
                }
            },
        );

        $this->publish(
            source: __DIR__ . '/AGENTS.md',
            destination: root_path('AGENTS.md'),
            callback: function (string $_, string $destination): void {
                $claude = root_path('CLAUDE.md');

                if (PHP_OS_FAMILY !== 'Windows') {
                    // @phpstan-ignore-next-line
                    exec("ln -s {$destination} {$claude}");
                }
            },
        );

        $this->updateComposer();

        $this->console->call('discovery:generate');

        $this->console->call('key:generate');

        if (! $tempest) {
            return;
        }

        if ($this->console->confirm('Do you want to install completions?', default: false)) {
            $this->console->call('completion:install');
        }
    }
}
