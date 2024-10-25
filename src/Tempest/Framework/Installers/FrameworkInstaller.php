<?php

declare(strict_types=1);

namespace Tempest\Framework\Installers;

use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use function Tempest\root_path;

final readonly class FrameworkInstaller implements Installer
{
    use PublishesFiles;

    public function getName(): string
    {
        return 'framework';
    }

    public function publishFiles(): void
    {
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
                    exec("chmod +x {$destination}");
                }
            },
        );
    }
}
