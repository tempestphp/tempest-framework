<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth;

use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class AuthInstallerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer->configure(
            __DIR__ . '/install',
            new ComposerNamespace('App\\', __DIR__ . '/install/App')
        );
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
            'UserPermissionMigration',
        ];

        foreach ($publishItems as $publishItem) {
            $path = "App/{$publishItem}.php";

            $this->installer
                ->assertFileExists($path)
                ->assertFileContains($path, 'namespace App;')
                ->assertFileNotContains($path, 'DoNotDiscover');
        }

        $this->installer->assertFileContains(
            'App/User.php',
            'use App\UserPermission',
        );
    }
}
