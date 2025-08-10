<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth\Authentication;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\AuthConfig;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Authentication\CanAuthenticate;
use Tempest\Auth\Authentication\SessionAuthenticator;
use Tempest\Clock\Clock;
use Tempest\Core\FrameworkKernel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\DateTime\Duration;
use Tempest\Http\Session\Config\FileSessionConfig;
use Tempest\Http\Session\Managers\FileSessionManager;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionManager;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class SessionAuthenticatorTest extends FrameworkIntegrationTestCase
{
    private string $path = __DIR__ . '/Fixtures/tmp';

    #[PreCondition]
    protected function configure(): void
    {
        Filesystem\ensure_directory_empty($this->path);

        $this->container->get(FrameworkKernel::class)->internalStorage = realpath($this->path);
        $this->container->config(new FileSessionConfig(path: 'sessions', expiration: Duration::hours(2)));
        $this->container->config(new AuthConfig(authenticatable: User::class));
        $this->container->singleton(SessionManager::class, fn () => new FileSessionManager(
            $this->container->get(Clock::class),
            $this->container->get(SessionConfig::class),
        ));

        $this->migrate(CreateMigrationsTable::class, CreateUsersTableMigration::class);
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        Filesystem\delete_directory($this->path);
    }

    #[Test]
    public function deauthenticated_by_default(): void
    {
        $authenticator = $this->container->get(Authenticator::class);
        $session = $this->container->get(Session::class);

        $this->assertNull($authenticator->current());
        $this->assertNull($session->get(SessionAuthenticator::AUTHENTICATABLE_KEY));
    }

    #[Test]
    public function can_authenticate(): void
    {
        $authenticator = $this->container->get(Authenticator::class);
        $session = $this->container->get(Session::class);

        $user = query(User::class)->create(full_name: 'Frieren the Slayer');
        $authenticator->authenticate($user);

        $this->assertInstanceOf(User::class, $authenticator->current());
        $this->assertSame($user->id->value, $session->get(SessionAuthenticator::AUTHENTICATABLE_KEY));
    }

    #[Test]
    public function can_deauthenticate(): void
    {
        $authenticator = $this->container->get(Authenticator::class);
        $session = $this->container->get(Session::class);

        $user = query(User::class)->create(full_name: 'Frieren the Slayer');
        $authenticator->authenticate($user);
        $authenticator->deauthenticate();

        $this->assertNull($authenticator->current());
        $this->assertNull($session->get(SessionAuthenticator::AUTHENTICATABLE_KEY));
    }
}

final class User implements CanAuthenticate
{
    public PrimaryKey $id;

    public function __construct(
        public string $full_name,
    ) {}
}

final class CreateUsersTableMigration implements MigratesUp
{
    public string $name = 'create_users_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('users')
            ->primary()
            ->string('full_name');
    }
}
