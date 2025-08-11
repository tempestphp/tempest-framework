<?php

namespace Tests\Tempest\Integration\Database;

use Tempest\Cryptography\Password\PasswordHasher;
use Tempest\Database\DatabaseMigration;
use Tempest\Database\Hashed;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class HashedAttributeTest extends FrameworkIntegrationTestCase
{
    private PasswordHasher $hasher {
        get => $this->container->get(PasswordHasher::class);
    }

    public function test_hashed_attribute_hashes_value_on_insert(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateUserWithHashTable::class);

        $user = new UserWithHash(
            email: 'test@example.com',
            password: 'plaintext-password', // @mago-expect security/no-literal-password
        )->save()->refresh();

        $this->assertNotSame('plaintext-password', $user->password);
        $this->assertTrue($this->hasher->verify('plaintext-password', $user->password));
    }

    public function test_hashed_attribute_hashes_value_on_update(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateUserWithHashTable::class);

        $user = new UserWithHash(
            email: 'test@example.com',
            password: 'original-password', // @mago-expect security/no-literal-password
        )->save()->refresh();

        $originalHash = $user->password;

        $user->update(password: 'new-password')->refresh(); // @mago-expect security/no-literal-password

        $this->assertNotSame('new-password', $user->password);
        $this->assertNotSame($originalHash, $user->password);

        $this->assertTrue($this->hasher->verify('new-password', $user->password));
        $this->assertFalse($this->hasher->verify('original-password', $user->password));
    }

    public function test_hashed_attribute_does_not_rehash_already_hashed_values(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateUserWithHashTable::class);

        $alreadyHashed = $this->hasher->hash('plaintext-password');

        $user = new UserWithHash(
            email: 'test@example.com',
            password: $alreadyHashed,
        )->save()->refresh();

        $this->assertSame($alreadyHashed, $user->password);
        $this->assertTrue($this->hasher->verify('plaintext-password', $user->password));
    }

    public function test_hashed_attribute_handles_null_values(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateUserWithNullablePasswordTable::class);

        $user = new UserWithNullablePassword(
            email: 'test@example.com',
            password: null,
        )->save()->refresh();

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

final class CreateUserWithHashTable implements DatabaseMigration
{
    public string $name = '2024_create_users_table';

    public function up(): CreateTableStatement
    {
        return CreateTableStatement::forModel(UserWithHash::class)
            ->primary()
            ->varchar('email')
            ->text('password');
    }

    public function down(): null
    {
        return null;
    }
}

final class CreateUserWithNullablePasswordTable implements DatabaseMigration
{
    public string $name = '2024_create_users_with_nullable_password_table';

    public function up(): CreateTableStatement
    {
        return CreateTableStatement::forModel(UserWithNullablePassword::class)
            ->primary()
            ->varchar('email')
            ->text('password', nullable: true);
    }

    public function down(): null
    {
        return null;
    }
}
