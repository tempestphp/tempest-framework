<?php

declare(strict_types=1);

namespace Tempest\Database\Tests;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Config\MysqlConfig;
use Tempest\Database\Config\PostgresConfig;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\OnDelete;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\RawStatement;

/**
 * @internal
 */
final class DatabaseQueryStatementTest extends TestCase
{
    #[DataProvider('provide_create_table_database_drivers')]
    #[Test]
    public function it_can_create_a_table(DatabaseConfig $driver, string $validSql): void
    {
        $statement = new CreateTableStatement('migrations', [
            new PrimaryKeyStatement(),
            new RawStatement('`name` VARCHAR(255) NOT NULL'),
        ])->compile($driver->dialect);

        $this->assertSame($validSql, $statement);
    }

    public static function provide_create_table_database_drivers(): Generator
    {
        yield 'mysql' => [
            new MysqlConfig(),
            'CREATE TABLE `migrations` (
    `id` INTEGER PRIMARY KEY AUTO_INCREMENT, 
    `name` VARCHAR(255) NOT NULL
);',
        ];

        yield 'postgresql' => [
            new PostgresConfig(),
            'CREATE TABLE `migrations` (
    `id` SERIAL PRIMARY KEY, 
    `name` VARCHAR(255) NOT NULL
);',
        ];

        yield 'sqlite' => [
            new SQLiteConfig(),
            'CREATE TABLE `migrations` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT, 
    `name` VARCHAR(255) NOT NULL
);',
        ];
    }

    #[DataProvider('provide_fk_create_table_database_drivers')]
    #[Test]
    public function it_can_create_a_foreign_key_constraint(DatabaseConfig $driver, string $validSql): void
    {
        $statement = new CreateTableStatement('books')
            ->primary()
            ->belongsTo('books.author_id', 'authors.id', OnDelete::CASCADE)
            ->varchar('name')
            ->compile($driver->dialect);

        $this->assertSame($validSql, $statement);
    }

    public static function provide_fk_create_table_database_drivers(): Generator
    {
        yield 'mysql' => [
            new MysqlConfig(),
            'CREATE TABLE `books` (
    `id` INTEGER PRIMARY KEY AUTO_INCREMENT, 
    `author_id` INTEGER  NOT NULL, 
    CONSTRAINT fk_authors_books_author_id FOREIGN KEY books(author_id) REFERENCES authors(id) ON DELETE CASCADE ON UPDATE NO ACTION, 
    `name` VARCHAR(255) NOT NULL
);',
        ];

        yield 'postgresql' => [
            new PostgresConfig(),
            'CREATE TABLE `books` (
    `id` SERIAL PRIMARY KEY, 
    `author_id` INTEGER  NOT NULL, 
    CONSTRAINT fk_authors_books_author_id FOREIGN KEY books(author_id) REFERENCES authors(id) ON DELETE CASCADE ON UPDATE NO ACTION, 
    `name` VARCHAR(255) NOT NULL
);',
        ];

        yield 'sqlite' => [
            new SQLiteConfig(),
            'CREATE TABLE `books` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT, 
    `author_id` INTEGER  NOT NULL, 
    `name` VARCHAR(255) NOT NULL
);',
        ];
    }

    public static function provide_database_driver(): Generator
    {
        yield 'mysql' => [
            new MysqlConfig(),
        ];

        yield 'postgresql' => [
            new PostgresConfig(),
        ];

        yield 'sqlite' => [
            new SQLiteConfig(),
        ];
    }

    public static function provide_alter_table_syntax(): Generator
    {
        yield 'mysql add statement' => [
            new MysqlConfig(),
            'ADD',
            'ALTER TABLE `authors` ADD `name` VARCHAR(255) NOT NULL;',
        ];

        yield 'postgresql add statement' => [
            new PostgresConfig(),
            'ADD',
            'ALTER TABLE `authors` ADD COLUMN `name` VARCHAR(255) NOT NULL;',
        ];

        yield 'sqlite add statement' => [
            new SQLiteConfig(),
            'ADD',
            'ALTER TABLE `authors` ADD COLUMN `name` VARCHAR(255) NOT NULL;',
        ];

        yield 'mysql delete statement' => [
            new MysqlConfig(),
            'DELETE',
            'ALTER TABLE `authors` DELETE `name` VARCHAR(255) NOT NULL;',
        ];

        yield 'postgresql delete statement' => [
            new PostgresConfig(),
            'DELETE',
            'ALTER TABLE `authors` DELETE COLUMN `name` VARCHAR(255) NOT NULL;',
        ];

        yield 'sqlite delete statement' => [
            new SQLiteConfig(),
            'DELETE',
            'ALTER TABLE `authors` DELETE COLUMN `name` VARCHAR(255) NOT NULL;',
        ];
    }
}
