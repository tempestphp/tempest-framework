<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth;

use Tempest\Auth\Authenticator;
use Tempest\Auth\Install\PermissionDatabaseMigration;
use Tempest\Auth\Install\User;
use Tempest\Auth\Install\UserDatabaseMigration;
use Tempest\Auth\Install\UserPermissionDatabaseMigration;
use Tempest\Clock\Clock;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Router\Session\Managers\FileSessionManager;
use Tempest\Router\Session\SessionConfig;
use Tempest\Router\Session\SessionManager;
use Tests\Tempest\Fixtures\Controllers\AdminController;
use Tests\Tempest\Integration\Auth\Fixtures\UserPermissionUnitEnum;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\uri;

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
            UserDatabaseMigration::class,
            PermissionDatabaseMigration::class,
            UserPermissionDatabaseMigration::class,
        );

        $this->path = __DIR__ . '/sessions';

        $this->container->config(new SessionConfig(path: $this->path));
        $this->container->singleton(
            SessionManager::class,
            fn () => new FileSessionManager(
                $this->container->get(Clock::class),
                $this->container->get(SessionConfig::class),
            ),
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
            ->get(uri([AdminController::class, 'customAuthorizer']))
            ->assertForbidden();

        $user->name = 'test';
        $user->save();

        $this->http
            ->get(uri([AdminController::class, 'customAuthorizer']))
            ->assertOk();
    }
}
