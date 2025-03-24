<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;

use function Tempest\src_path;

final class TestInstaller implements Installer
{
    use PublishesFiles;

    private(set) string $name = 'test';

    public function install(): void
    {
        $this->publish(
            __DIR__ . '/TestInstallerClass.php',
            src_path('Foo/Bar/TestInstallerClass.php'),
        );

        $this->publish(
            __DIR__ . '/TestInstallerFile.html',
            src_path('View/TestInstallerFile.html'),
        );
    }
}
