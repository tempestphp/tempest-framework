<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth\Authentication;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\AuthConfig;
use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Authentication\SessionAuthenticator;
use Tempest\Auth\Exceptions\AuthenticatableWasMissing;
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
use Tempest\Http\Session\SessionManager;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class SessionAuthenticatorTest extends FrameworkIntegrationTestCase
{
    private string $path = __DIR__ . '/tmp';

    #[PreCondition]
    protected function configure(): void
    {
        Filesystem\ensure_directory_empty($this->path);

        $this->container->get(FrameworkKernel::class)->internalStorage = realpath($this->path);
        $this->container->config(new FileSessionConfig(path: 'sessions', expiration: Duration::hours(2)));
        $this->container->config(new AuthConfig(authenticatables: [User::class]));
        $this->container->singleton(SessionManager::class, fn () => new FileSessionManager(
            $this->container->get(Clock::class),
            $this->container->get(FileSessionConfig::class),
        ));

        $this->database->migrate(CreateMigrationsTable::class, CreateUsersTableMigration::class, CreateApiKeysTableMigration::class);
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
        $this->assertNull($session->get(SessionAuthenticator::AUTHENTICATABLE_CLASS));
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
        $this->assertSame(User::class, $session->get(SessionAuthenticator::AUTHENTICATABLE_CLASS));
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
        $this->assertNull($session->get(SessionAuthenticator::AUTHENTICATABLE_CLASS));
    }

    #[Test]
    public function multiple_authenticatables(): void
    {
        $authenticator = $this->container->get(Authenticator::class);

        $user = query(User::class)->create(full_name: 'Frieren the Slayer');
        $apiKey = query(ApiKey::class)->create(description: 'API key for Frieren');

        $authenticator->authenticate($user);
        $this->assertInstanceOf(User::class, $authenticator->current());
        $this->assertInstanceOf(User::class, $this->container->invoke(ServiceWithAuthenticatable::class));
        $this->assertSame('Frieren the Slayer', $authenticator->current()->full_name);
        $authenticator->deauthenticate();

        $authenticator->authenticate($apiKey);
        $this->assertInstanceOf(ApiKey::class, $authenticator->current());
        $this->assertInstanceOf(ApiKey::class, $this->container->invoke(ServiceWithAuthenticatable::class));
        $this->assertSame('API key for Frieren', $authenticator->current()->description);
        $authenticator->deauthenticate();

        $this->assertNull($authenticator->current());

        $this->assertException(AuthenticatableWasMissing::class, fn () => $this->container->invoke(ServiceWithAuthenticatable::class));
    }
}

final class ServiceWithAuthenticatable
{
    // TODO: User|ApiKey should work, but it currently yields a circular dependency error.
    public function __invoke(Authenticatable $authenticatable): object
    {
        return $authenticatable;
    }
}

final class User implements Authenticatable
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

final class ApiKey implements Authenticatable
{
    public PrimaryKey $id;

    public function __construct(
        public string $description,
    ) {}
}

final class CreateApiKeysTableMigration implements MigratesUp
{
    public string $name = 'create_api_keys_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('api_keys')
            ->primary()
            ->string('description');
    }
}
