<?php

declare(strict_types=1);

namespace Tempest\Console\Installers;

use Tempest\Core\Installer;
use Tempest\Core\IsComponentInstaller;

use function Tempest\root_path;

final class ConsoleInstaller implements Installer
{
    use IsComponentInstaller;

    private(set) string $name = 'console';

    public function install(): void
    {
        $this->installMainNamespace();

        $this->publish(
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
    }
}
