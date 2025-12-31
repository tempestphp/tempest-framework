<?php

namespace Tests\Tempest\Integration\Auth\Authentication;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\AuthConfig;
use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Auth\Authentication\AuthenticatableResolver;
use Tempest\Auth\Exceptions\AuthenticatableModelWasInvalid;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class DatabaseAuthenticatableResolverTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function can_resolve_custom_authenticatable_class(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateApiTokensTableMigration::class);

        $this->container->config(new AuthConfig(authenticatables: [ApiToken::class]));

        $authenticatable = query(ApiToken::class)->create();

        $authenticatableResolver = $this->container->get(AuthenticatableResolver::class);
        $resolved = $authenticatableResolver->resolve($authenticatable->id, ApiToken::class);

        // @phpstan-ignore property.notFound
        $this->assertEquals($authenticatable->id, $resolved->id);
    }

    #[Test]
    public function can_resolve_id_from_custom_authenticatable_class(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateApiTokensTableMigration::class);

        $this->container->config(new AuthConfig(authenticatables: [ApiToken::class]));

        $authenticatable = query(ApiToken::class)->create(id: 10);

        $authenticatableResolver = $this->container->get(AuthenticatableResolver::class);
        $resolved = $authenticatableResolver->resolveId($authenticatable);

        $this->assertEquals(10, $resolved);
    }

    #[Test]
    public function throws_if_primary_key_is_not_initialized(): void
    {
        $this->expectException(AuthenticatableModelWasInvalid::class);

        $authenticatableResolver = $this->container->get(AuthenticatableResolver::class);
        $authenticatableResolver->resolveId(new ApiToken());
    }

    #[Test]
    public function throws_if_model_has_no_primary_key(): void
    {
        $this->expectException(AuthenticatableModelWasInvalid::class);

        $authenticatableResolver = $this->container->get(AuthenticatableResolver::class);
        $authenticatableResolver->resolveId(new class implements Authenticatable {});
    }
}

final class ApiToken implements Authenticatable
{
    public PrimaryKey $id;
}

final class CreateApiTokensTableMigration implements MigratesUp
{
    public string $name = 'create_api_tokens_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('api_tokens')
            ->primary();
    }
}
