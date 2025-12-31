<?php

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\Test;
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

use function Tempest\Database\query;

final class EncryptedAttributeTest extends FrameworkIntegrationTestCase
{
    private Encrypter $encrypter {
        get => $this->container->get(Encrypter::class);
    }

    #[Test]
    public function encrypts_value_on_insert(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateUserWithEncryptedDataTable::class);

        $user = query(UserWithEncryptedData::class)->create(
            email: 'test@example.com',
            secret: 'sensitive information', // @mago-expect lint:no-literal-password
        );

        $this->assertSame('sensitive information', $user->secret);

        $encrypted = new Query('SELECT secret FROM users WHERE email = ?', ['test@example.com'])->fetchFirst();

        $this->assertNotSame('sensitive information', $encrypted['secret']);
    }

    #[Test]
    public function encrypts_value_on_update(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateUserWithEncryptedDataTable::class);

        $user = query(UserWithEncryptedData::class)->create(
            email: 'test@example.com',
            secret: 'original secret', // @mago-expect lint:no-literal-password
        );

        $user->update(secret: 'new secret')->refresh(); // @mago-expect lint:no-literal-password

        $this->assertSame('new secret', $user->secret);

        $encrypted = new Query('SELECT secret FROM users WHERE email = ?', ['test@example.com'])->fetchFirst();

        $this->assertNotSame('original secret', $encrypted['secret']);
        $this->assertNotSame('new secret', $encrypted['secret']);
    }

    #[Test]
    public function does_not_re_encrypt_already_encrypted_values(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateUserWithEncryptedDataTable::class);

        $user = query(UserWithEncryptedData::class)->create(
            email: 'test@example.com',
            secret: $this->encrypter->encrypt('sensitive data'),
        );

        $this->assertSame('sensitive data', $user->secret);
    }

    #[Test]
    public function handles_null_values(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateUserWithNullableEncryptedDataTable::class);

        $user = query(UserWithNullableEncryptedData::class)->create(
            email: 'test@example.com',
            secret: null,
        );

        $this->assertNull($user->secret);
    }

    #[Test]
    public function handles_empty_strings(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateUserWithEncryptedDataTable::class);

        $user = query(UserWithEncryptedData::class)->create(
            email: 'test@example.com',
            secret: '',
        );

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
            ->string('email')
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
            ->string('email')
            ->text('secret', nullable: true);
    }
}
