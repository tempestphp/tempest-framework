<?php

declare(strict_types=1);

namespace Integration\Auth;

use Tempest\Auth\CreatePermissionsTable;
use Tempest\Auth\CreateUserPermissionTable;
use Tempest\Auth\CreateUsersTable;
use Tempest\Auth\Permission;
use Tempest\Auth\User;
use Tempest\Auth\UserPermission;
use Tempest\Clock\Clock;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\Session\Managers\FileSessionManager;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionManager;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class AuthorizerTest extends FrameworkIntegrationTestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = __DIR__ . '/sessions';

        $this->container->config(new SessionConfig(path: $this->path));
        $this->container->singleton(
            SessionManager::class,
            fn () => new FileSessionManager(
                $this->container->get(Clock::class),
                $this->container->get(SessionConfig::class),
            ),
        );

        $this->migrate(
            CreateMigrationsTable::class,
            CreateUsersTable::class,
            CreatePermissionsTable::class,
            CreateUserPermissionTable::class,
        );
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob("{$this->path}/*"));
        rmdir($this->path);
    }

    public function test_authorize(): void
    {
        $user = (new User(
            name: 'Brent',
            email: 'brendt@stitcher.io',
        ))
            ->setPassword('password')
            ->save();

        $permission = (new Permission(name: 'admin'))->save();

        $userPermission = (new UserPermission(
            user: $user,
            permission: $permission
        ))->save();

        $user->load('userPermissions.permission');
    }
}
