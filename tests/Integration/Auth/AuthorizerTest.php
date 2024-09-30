<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth;

use Tempest\Auth\Authenticator;
use Tempest\Auth\CreatePermissionsTable;
use Tempest\Auth\CreateUserPermissionTable;
use Tempest\Auth\CreateUsersTable;
use Tempest\Auth\User;
use Tempest\Clock\Clock;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\Session\Managers\FileSessionManager;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionManager;
use function Tempest\uri;
use Tests\Tempest\Fixtures\Controllers\AdminController;
use Tests\Tempest\Integration\Auth\Fixtures\UserPermissionUnitEnum;
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

        $this->migrate(
            CreateMigrationsTable::class,
            CreateUsersTable::class,
            CreatePermissionsTable::class,
            CreateUserPermissionTable::class,
        );

        $this->path = __DIR__ . '/sessions';

        $this->container->config(new SessionConfig(path: $this->path));
        $this->container->singleton(
            SessionManager::class,
            fn () => new FileSessionManager(
                $this->container->get(Clock::class),
                $this->container->get(SessionConfig::class)
            )
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
            ->save()
            ->grantPermission(UserPermissionUnitEnum::ADMIN);

        $this->http
            ->get(uri([AdminController::class, 'admin']))
            ->assertForbidden();

        $authenticator = $this->container->get(Authenticator::class);

        $authenticator->login($user);

        $this->http
            ->get(uri([AdminController::class, 'admin']))
            ->assertOk();

        $this->http
            ->get(uri([AdminController::class, 'guest']))
            ->assertForbidden();

        $this->http
            ->get(uri([AdminController::class, 'custom_authorizer']))
            ->assertForbidden();

        $user->name = 'test';
        $user->save();

        $this->http
            ->get(uri([AdminController::class, 'custom_authorizer']))
            ->assertOk();
    }
}
