<?php

namespace Tests\Tempest\Integration\Auth\Authentication;

use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\AuthConfig;
use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Exceptions\AuthenticatableWasMissing;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class CurrentAuthenticatableTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateServiceAccountTableMigration::class);

        $this->container->config(new AuthConfig(authenticatables: [ServiceAccount::class]));
    }

    #[Test]
    public function throws_if_not_authenticated(): void
    {
        $this->expectException(AuthenticatableWasMissing::class);

        $this->container->get(ServiceAccount::class);
    }

    #[Test]
    public function resolves_correct_model(): void
    {
        $account1 = query(ServiceAccount::class)->create();
        $account2 = query(ServiceAccount::class)->create();

        $authenticator = $this->container->get(Authenticator::class);

        $authenticator->authenticate($account2);
        $this->assertEquals($account2, $this->container->get(ServiceAccount::class));

        $authenticator->authenticate($account1);
        $this->assertEquals($account1, $this->container->get(ServiceAccount::class));
    }
}

final class ServiceAccount implements Authenticatable
{
    public PrimaryKey $id;
}

final class CreateServiceAccountTableMigration implements MigratesUp
{
    public string $name = 'create_service_accounts_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('service_accounts')
            ->primary();
    }
}
