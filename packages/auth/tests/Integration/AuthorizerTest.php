<?php

declare(strict_types=1);

namespace Tempest\Auth\Tests\Integration;

use Tempest\Auth\Authenticator;
use Tempest\Auth\Install\CreatePermissionsTable;
use Tempest\Auth\Install\CreateUserPermissionsTable;
use Tempest\Auth\Install\CreateUsersTable;
use Tempest\Auth\Install\User;
use Tempest\Auth\Tests\Integration\Fixtures\UserPermissionUnitEnum;
use Tempest\Clock\Clock;
use Tempest\Core\FrameworkKernel;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\Session\Managers\FileSessionManager;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionManager;
use Tempest\Support\Filesystem;
use Tests\Tempest\Fixtures\Controllers\AdminController;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\Filesystem;
use function Tempest\uri;

/**
 * @internal
 */
final class AuthorizerTest extends FrameworkIntegrationTestCase
{
    private string $path = __DIR__ . '/Fixtures/tmp';

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate(
            CreateMigrationsTable::class,
            CreateUsersTable::class,
            CreatePermissionsTable::class,
            CreateUserPermissionsTable::class,
        );

        $this->path = __DIR__ . '/Fixtures/tmp';

        Filesystem\ensure_directory_empty($this->path);

        $this->container->get(FrameworkKernel::class)->internalStorage = realpath($this->path);

        $this->container->config(new SessionConfig(path: 'sessions'));
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
        Filesystem\delete_directory($this->path);
    }

    public function test_authorize(): void
    {
        $user = new User(
            name: 'Brent',
            email: 'brendt@stitcher.io',
        )
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
