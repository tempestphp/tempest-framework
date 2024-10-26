<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tempest\Core\Composer;
use Tempest\Core\ComposerNamespace;
use Tempest\Core\Kernel;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class AuthInstallerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer->setNamespace(new ComposerNamespace('App\\', __DIR__ . '/install'));
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    public function test_install_auth(): void
    {
        $this->console->call('install auth --force');

        $publishItems = [
            'User',
            'UserMigration',
            'Permission',
            'PermissionMigration',
            'UserPermission',
            'UserPermissionMigrations',
        ];

        foreach ($publishItems as $publishItem) {
            $path = "{$publishItem}.php";

            $this->installer
                ->assertFileExists($path)
                ->assertFileContains($path, 'namespace App;')
                ->assertFileNotContains($path, 'DoNotDiscover');
        }
    }
}
