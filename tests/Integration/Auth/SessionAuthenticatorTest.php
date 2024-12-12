<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth;

use Tempest\Auth\Authenticator;
use Tempest\Auth\CurrentUserNotLoggedIn;
use Tempest\Auth\Install\PermissionDatabaseMigration;
use Tempest\Auth\Install\User;
use Tempest\Auth\Install\UserDatabaseMigration;
use Tempest\Auth\Install\UserPermissionDatabaseMigration;
use Tempest\Auth\SessionAuthenticator;
use Tempest\Clock\Clock;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Router\Session\Managers\FileSessionManager;
use Tempest\Router\Session\Session;
use Tempest\Router\Session\SessionConfig;
use Tempest\Router\Session\SessionManager;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class SessionAuthenticatorTest extends FrameworkIntegrationTestCase
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
            UserDatabaseMigration::class,
            PermissionDatabaseMigration::class,
            UserPermissionDatabaseMigration::class,
        );
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob("{$this->path}/*"));
        rmdir($this->path);
    }

    public function test_authenticator(): void
    {
        $auth = $this->container->get(Authenticator::class);

        $this->assertInstanceOf(SessionAuthenticator::class, $auth);

        $this->assertNull($auth->currentUser());

        $user = (new User('Brent', 'brendt@stitcher.io'))
            ->setPassword('password')
            ->save();

        $auth->login($user);

        // Current user via authenticator
        $this->assertTrue($auth->currentUser()->getId()->equals($user->id));

        // Current user via session
        $session = $this->container->get(Session::class);
        $this->assertTrue($auth->currentUser()->getId()->equals($session->get('tempest_session_user')));

        // Current user via container
        $this->assertTrue($auth->currentUser()->getId()->equals($this->container->get(User::class)->id));

        $auth->logout();

        // Auth is empty
        $this->assertNull($auth->currentUser());

        // Session is empty
        $this->assertNull($session->get('tempest_session_user'));

        // Container user throws
        $this->expectException(CurrentUserNotLoggedIn::class);
        $this->assertNull($this->container->get(User::class));
    }
}
