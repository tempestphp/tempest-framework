<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth;

use Tempest\Core\Commands\InstallCommand;
use Tempest\Support\Namespace\Psr4Namespace;
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
            new Psr4Namespace('App\\', __DIR__ . '/install/App'),
        );
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    public function test_install_auth(): void
    {
        $this->console->call(InstallCommand::class, ['auth', '--force']);

        $publishItems = [
            'User',
            'Permission',
            'UserPermission',
            'CreateUsersTable',
            'CreatePermissionsTable',
            'CreateUserPermissionsTable',
        ];

        foreach ($publishItems as $publishItem) {
            $path = "App/Auth/{$publishItem}.php";

            $this->installer
                ->assertFileExists($path)
                ->assertFileContains($path, 'namespace App\Auth;')
                ->assertFileNotContains($path, 'SkipDiscovery');
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
