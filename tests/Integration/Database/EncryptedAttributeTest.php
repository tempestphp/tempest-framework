<?php

namespace Tests\Tempest\Integration\Database;

use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Database\Encrypted;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class EncryptedAttributeTest extends FrameworkIntegrationTestCase
{
    private Encrypter $encrypter {
        get => $this->container->get(Encrypter::class);
    }

    public function test_encrypted_attribute_encrypts_value_on_insert(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateUserWithEncryptedDataTable::class);

        $user = new UserWithEncryptedData(
            email: 'test@example.com',
            secret: 'sensitive information',
        )->save()->refresh();

        $this->assertSame('sensitive information', $user->secret);

        $encrypted = new Query('SELECT secret FROM users WHERE email = ?', ['test@example.com'])->fetchFirst();

        $this->assertNotSame('sensitive information', $encrypted['secret']);
    }

    public function test_encrypted_attribute_encrypts_value_on_update(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateUserWithEncryptedDataTable::class);

        $user = new UserWithEncryptedData(
            email: 'test@example.com',
            secret: 'original secret',
        )->save()->refresh();

        $user->update(secret: 'new secret')->refresh();

        $this->assertSame('new secret', $user->secret);

        $encrypted = new Query('SELECT secret FROM users WHERE email = ?', ['test@example.com'])->fetchFirst();

        $this->assertNotSame('original secret', $encrypted['secret']);
        $this->assertNotSame('new secret', $encrypted['secret']);
    }

    public function test_encrypted_attribute_does_not_re_encrypt_already_encrypted_values(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateUserWithEncryptedDataTable::class);

        $alreadyEncrypted = $this->encrypter->encrypt('sensitive data');

        $user = new UserWithEncryptedData(
            email: 'test@example.com',
            secret: $alreadyEncrypted,
        )->save()->refresh();

        $this->assertSame('sensitive data', $user->secret);
    }

    public function test_encrypted_attribute_handles_null_values(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateUserWithNullableEncryptedDataTable::class);

        $user = new UserWithNullableEncryptedData(
            email: 'test@example.com',
            secret: null,
        )->save()->refresh();

        $this->assertNull($user->secret);
    }

    public function test_encrypted_attribute_handles_empty_strings(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateUserWithEncryptedDataTable::class);

        $user = new UserWithEncryptedData(
            email: 'test@example.com',
            secret: '',
        )->save()->refresh();

        $this->assertSame('', $user->secret);
    }
}

#[Table('users')]
final class UserWithEncryptedData
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public function __construct(
        public string $email,
        #[Encrypted]
        public string $secret,
    ) {}
}

#[Table('users')]
final class UserWithNullableEncryptedData
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public function __construct(
        public string $email,
        #[Encrypted]
        public ?string $secret,
    ) {}
}

final class CreateUserWithEncryptedDataTable implements MigratesUp
{
    public string $name = '2024_create_users_with_encrypted_data_table';

    public function up(): CreateTableStatement
    {
        return CreateTableStatement::forModel(UserWithEncryptedData::class)
            ->primary()
            ->varchar('email')
            ->text('secret');
    }
}

final class CreateUserWithNullableEncryptedDataTable implements MigratesUp
{
    public string $name = '2024_create_users_with_nullable_encrypted_data_table';

    public function up(): CreateTableStatement
    {
        return CreateTableStatement::forModel(UserWithNullableEncryptedData::class)
            ->primary()
            ->varchar('email')
            ->text('secret', nullable: true);
    }
}
