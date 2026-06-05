<?php

declare(strict_types=1);

namespace Tempest\Console\Installers;

use Tempest\Core\Installer;
use Tempest\Core\IsComponentInstaller;

use function Tempest\root_path;

final class ConsoleInstaller
{
    use IsComponentInstaller;

    #[Installer('Console', alias: 'console')]
    public function install(): void
    {
        $this->installMainNamespace();

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

        $this->updateComposer();

        if (! $tempest) {
            return;
        }

        if ($this->console->confirm('Do you want to install completions?', default: false)) {
            $this->console->call('completion:install');
        }
    }
}
