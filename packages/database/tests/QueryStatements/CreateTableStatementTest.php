<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\QueryStatements;

use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\OnDelete;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\RawStatement;

/**
 * @internal
 */
final class CreateTableStatementTest extends TestCase
{
    #[DataProvider('provide_create_table_database_dialects')]
    #[Test]
    public function create_a_table(DatabaseDialect $dialect, string $validSql): void
    {
        $statement = new CreateTableStatement('migrations', [
            new PrimaryKeyStatement(),
            new RawStatement('`name` VARCHAR(255) NOT NULL'),
        ])->compile($dialect);

        $this->assertSame($validSql, $statement);
    }

    public static function provide_create_table_database_dialects(): iterable
    {
        yield 'mysql' => [
            DatabaseDialect::MYSQL,
            <<<SQL
            CREATE TABLE `migrations` (
                `id` INTEGER PRIMARY KEY AUTO_INCREMENT, 
                `name` VARCHAR(255) NOT NULL
            );
            SQL,
        ];

        yield 'postgresql' => [
            DatabaseDialect::POSTGRESQL,
            <<<SQL
            CREATE TABLE "migrations" (
                "id" SERIAL PRIMARY KEY, 
                `name` VARCHAR(255) NOT NULL
            );
            SQL,
        ];

        yield 'sqlite' => [
            DatabaseDialect::SQLITE,
            <<<SQL
            CREATE TABLE `migrations` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT, 
                `name` VARCHAR(255) NOT NULL
            );
            SQL,
        ];
    }

    #[DataProvider('provide_fk_create_table_database_drivers')]
    #[Test]
    public function create_a_foreign_key_constraint(DatabaseDialect $dialect, string $validSql): void
    {
        $statement = new CreateTableStatement('books')
            ->primary()
            ->belongsTo('books.author_id', 'authors.id', OnDelete::CASCADE)
            ->varchar('name')
            ->compile($dialect);

        $this->assertSame($validSql, $statement);

        $statement = new CreateTableStatement('books')
            ->primary()
            ->foreignId('author_id', constrainedOn: 'authors', onDelete: OnDelete::CASCADE)
            ->varchar('name')
            ->compile($dialect);

        $this->assertSame($validSql, $statement);

        $statement = new CreateTableStatement('books')
            ->primary()
            ->foreignId('books.author_id', constrainedOn: 'authors.id', onDelete: OnDelete::CASCADE)
            ->varchar('name')
            ->compile($dialect);

        $this->assertSame($validSql, $statement);
    }

    public static function provide_fk_create_table_database_drivers(): Generator
    {
        yield 'mysql' => [
            DatabaseDialect::MYSQL,
            <<<SQL
            CREATE TABLE `books` (
                `id` INTEGER PRIMARY KEY AUTO_INCREMENT, 
                `author_id` INTEGER  NOT NULL, 
                CONSTRAINT `fk_authors_books_author_id` FOREIGN KEY books(author_id) REFERENCES authors(id) ON DELETE CASCADE ON UPDATE NO ACTION, 
                `name` VARCHAR(255) NOT NULL
            );
            SQL,
        ];

        yield 'postgresql' => [
            DatabaseDialect::POSTGRESQL,
            <<<SQL
            CREATE TABLE "books" (
                "id" SERIAL PRIMARY KEY, 
                "author_id" INTEGER  NOT NULL, 
                CONSTRAINT "fk_authors_books_author_id" FOREIGN KEY(author_id) REFERENCES authors(id) ON DELETE CASCADE ON UPDATE NO ACTION, 
                "name" VARCHAR(255) NOT NULL
            );
            SQL,
        ];

        yield 'sqlite' => [
            DatabaseDialect::SQLITE,
            <<<SQL
            CREATE TABLE `books` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT, 
                `author_id` INTEGER  NOT NULL, 
                `name` VARCHAR(255) NOT NULL
            );
            SQL,
        ];
    }

    #[DataProvider('provide_fk_create_table_database_drivers_explicit')]
    #[Test]
    public function create_a_foreign_key_constraint_with_explicit_column(DatabaseDialect $dialect, string $validSql): void
    {
        $statement = new CreateTableStatement('books')
            ->primary()
            ->integer('author_id')
            ->foreignKey('books.author_id', 'authors.id', OnDelete::CASCADE)
            ->varchar('name')
            ->compile($dialect);

        $this->assertSame($validSql, $statement);
    }

    public static function provide_fk_create_table_database_drivers_explicit(): Generator
    {
        yield 'mysql' => [
            DatabaseDialect::MYSQL,
            <<<SQL
            CREATE TABLE `books` (
                `id` INTEGER PRIMARY KEY AUTO_INCREMENT, 
                `author_id` INTEGER  NOT NULL, 
                CONSTRAINT `fk_authors_books_author_id` FOREIGN KEY books(author_id) REFERENCES authors(id) ON DELETE CASCADE ON UPDATE NO ACTION, 
                `name` VARCHAR(255) NOT NULL
            );
            SQL,
        ];

        yield 'postgresql' => [
            DatabaseDialect::POSTGRESQL,
            <<<SQL
            CREATE TABLE "books" (
                "id" SERIAL PRIMARY KEY, 
                "author_id" INTEGER  NOT NULL, 
                CONSTRAINT "fk_authors_books_author_id" FOREIGN KEY(author_id) REFERENCES authors(id) ON DELETE CASCADE ON UPDATE NO ACTION, 
                "name" VARCHAR(255) NOT NULL
            );
            SQL,
        ];

        yield 'sqlite' => [
            DatabaseDialect::SQLITE,
            <<<SQL
            CREATE TABLE `books` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT, 
                `author_id` INTEGER  NOT NULL, 
                `name` VARCHAR(255) NOT NULL
            );
            SQL,
        ];
    }

    #[DataProvider('provide_uuid_primary_database_dialects')]
    #[Test]
    public function create_table_with_uuid_primary(DatabaseDialect $dialect, string $validSql): void
    {
        $uuid = new CreateTableStatement('users')
            ->uuid('uuid')
            ->text('name')
            ->text('email')
            ->compile($dialect);

        $primary = new CreateTableStatement('users')
            ->primary('uuid', uuid: true)
            ->text('name')
            ->text('email')
            ->compile($dialect);

        $this->assertSame($validSql, $uuid);
        $this->assertSame($validSql, $primary);
    }

    public static function provide_uuid_primary_database_dialects(): iterable
    {
        yield 'mysql' => [
            DatabaseDialect::MYSQL,
            <<<SQL
            CREATE TABLE `users` (
                `uuid` CHAR(36) PRIMARY KEY, 
                `name` TEXT NOT NULL, 
                `email` TEXT NOT NULL
            );
            SQL,
        ];

        yield 'postgresql' => [
            DatabaseDialect::POSTGRESQL,
            <<<SQL
            CREATE TABLE "users" (
                "uuid" UUID PRIMARY KEY, 
                "name" TEXT NOT NULL, 
                "email" TEXT NOT NULL
            );
            SQL,
        ];

        yield 'sqlite' => [
            DatabaseDialect::SQLITE,
            <<<SQL
            CREATE TABLE `users` (
                `uuid` TEXT PRIMARY KEY, 
                `name` TEXT NOT NULL, 
                `email` TEXT NOT NULL
            );
            SQL,
        ];
    }

    #[DataProvider('provide_datetime_current_database_dialects')]
    #[Test]
    public function datetime_current_default(DatabaseDialect $dialect, string $validSql): void
    {
        $statement = new CreateTableStatement('users')
            ->datetime('created_at', current: true)
            ->compile($dialect);

        $this->assertSame($validSql, $statement);
    }

    #[Test]
    public function datetime_current_conflicts_with_default(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CreateTableStatement('users')
            ->datetime('created_at', default: '2000-01-01 00:00:00', current: true)
            ->compile(DatabaseDialect::MYSQL);
    }

    public static function provide_datetime_current_database_dialects(): iterable
    {
        yield 'mysql' => [
            DatabaseDialect::MYSQL,
            <<<SQL
            CREATE TABLE `users` (
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
            );
            SQL,
        ];

        yield 'postgresql' => [
            DatabaseDialect::POSTGRESQL,
            <<<SQL
            CREATE TABLE "users" (
                "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
            );
            SQL,
        ];

        yield 'sqlite' => [
            DatabaseDialect::SQLITE,
            <<<SQL
            CREATE TABLE `users` (
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
            );
            SQL,
        ];
    }
}
