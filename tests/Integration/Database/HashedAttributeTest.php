<?php

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Cryptography\Password\PasswordHasher;
use Tempest\Database\Hashed;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class HashedAttributeTest extends FrameworkIntegrationTestCase
{
    private PasswordHasher $hasher {
        get => $this->container->get(PasswordHasher::class);
    }

    #[Test]
    public function hashes_value_on_insert(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateUserWithHashTable::class);

        $user = query(UserWithHash::class)->create(
            email: 'test@example.com',
            password: 'plaintext-password', // @mago-expect lint:no-literal-password
        );

        // The current behavior when creating a model is to not refresh it.
        // In this case, it might be a potential security issue?
        $this->assertSame('plaintext-password', $user->password);

        $user->refresh();

        $this->assertNotSame('plaintext-password', $user->password);
        $this->assertTrue($this->hasher->verify('plaintext-password', $user->password));
    }

    #[Test]
    public function hashes_value_on_update(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateUserWithHashTable::class);

        $user = query(UserWithHash::class)
            ->create(
                email: 'test@example.com',
                password: 'original-password', // @mago-expect lint:no-literal-password
            )
            ->refresh();

        $originalHash = $user->password;

        $user->update(password: 'new-password')->refresh(); // @mago-expect lint:no-literal-password

        $this->assertNotSame('new-password', $user->password);
        $this->assertNotSame($originalHash, $user->password);

        $this->assertTrue($this->hasher->verify('new-password', $user->password));
        $this->assertFalse($this->hasher->verify('original-password', $user->password));
    }

    #[Test]
    public function does_not_rehash_already_hashed_values(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateUserWithHashTable::class);

        $user = query(UserWithHash::class)
            ->create(
                email: 'test@example.com',
                password: $alreadyHashed = $this->hasher->hash('plaintext-password'),
            )
            ->refresh();

        $this->assertSame($alreadyHashed, $user->password);
        $this->assertTrue($this->hasher->verify('plaintext-password', $user->password));
    }

    #[Test]
    public function handles_null_values(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateUserWithNullablePasswordTable::class);

        $user = query(UserWithNullablePassword::class)
            ->create(
                email: 'test@example.com',
                password: null,
            )
            ->refresh();

        $this->assertNull($user->password);
    }
}

final class UserWithHash
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public function __construct(
        public string $email,
        #[Hashed]
        public string $password,
    ) {}
}

final class UserWithNullablePassword
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public function __construct(
        public string $email,
        #[Hashed]
        public ?string $password,
    ) {}
}

final class CreateUserWithHashTable implements MigratesUp
{
    public string $name = '2024_create_users_table';

    public function up(): CreateTableStatement
    {
        return CreateTableStatement::forModel(UserWithHash::class)
            ->primary()
            ->varchar('email')
            ->text('password');
    }
}

final class CreateUserWithNullablePasswordTable implements MigratesUp
{
    public string $name = '2024_create_users_with_nullable_password_table';

    public function up(): CreateTableStatement
    {
        return CreateTableStatement::forModel(UserWithNullablePassword::class)
            ->primary()
            ->varchar('email')
            ->text('password', nullable: true);
    }
}
