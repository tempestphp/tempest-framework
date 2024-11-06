<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth;

use Tempest\Core\Composer;
use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\get;

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
            $path = "App/Auth/{$publishItem}.php";

            $this->installer
                ->assertFileExists($path)
                ->assertFileContains($path, 'namespace App\Auth;')
                ->assertFileNotContains($path, 'DoNotDiscover');
        }

        $this->installer->assertFileContains(
            'App/Auth/User.php',
            'use App\Auth\UserPermission',
        );

        $this->installer->assertFileContains(
            'App/Auth/User.php',
            '/** @var \App\Auth\UserPermission[] $userPermissions */',
        );
    }
}
