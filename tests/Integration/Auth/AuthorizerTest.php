<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth;

use Tempest\Auth\Authenticator;
use Tempest\Auth\Install\CreatePermissionsTable;
use Tempest\Auth\Install\CreateUserPermissionsTable;
use Tempest\Auth\Install\CreateUsersTable;
use Tempest\Auth\Install\User;
use Tempest\Clock\Clock;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Filesystem\LocalFilesystem;
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
    private string $path = __DIR__ . '/Fixtures/tmp';

    private LocalFilesystem $filesystem;

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
        $this->filesystem = new LocalFilesystem();
        $this->filesystem->deleteDirectory($this->path, recursive: true);
        $this->filesystem->ensureDirectoryExists($this->path);

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
        $this->filesystem->deleteDirectory($this->path, recursive: true);
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
