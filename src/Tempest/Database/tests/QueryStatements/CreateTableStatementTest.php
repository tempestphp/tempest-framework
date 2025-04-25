<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\QueryStatements;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
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
#[CoversClass(CreateTableStatement::class)]
final class CreateTableStatementTest extends TestCase
{
    #[DataProvider('provide_create_table_database_dialects')]
    public function test_create_a_table(DatabaseDialect $dialect, string $validSql): void
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
            'CREATE TABLE `migrations` (
    `id` INTEGER PRIMARY KEY AUTO_INCREMENT, 
    `name` VARCHAR(255) NOT NULL
);',
        ];

        yield 'postgresql' => [
            DatabaseDialect::POSTGRESQL,
            'CREATE TABLE `migrations` (
    `id` SERIAL PRIMARY KEY, 
    `name` VARCHAR(255) NOT NULL
);',
        ];

        yield 'sqlite' => [
            DatabaseDialect::SQLITE,
            'CREATE TABLE `migrations` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT, 
    `name` VARCHAR(255) NOT NULL
);',
        ];
    }

    #[DataProvider('provide_fk_create_table_database_drivers')]
    public function test_create_a_foreign_key_constraint(DatabaseDialect $dialect, string $validSql): void
    {
        $statement = new CreateTableStatement('books')
            ->primary()
            ->belongsTo('books.author_id', 'authors.id', OnDelete::CASCADE)
            ->varchar('name')
            ->compile($dialect);

        $this->assertSame($validSql, $statement);
    }

    public static function provide_fk_create_table_database_drivers(): Generator
    {
        yield 'mysql' => [
            DatabaseDialect::MYSQL,
            'CREATE TABLE `books` (
    `id` INTEGER PRIMARY KEY AUTO_INCREMENT, 
    `author_id` INTEGER  NOT NULL, 
    CONSTRAINT `fk_authors_books_author_id` FOREIGN KEY books(author_id) REFERENCES authors(id) ON DELETE CASCADE ON UPDATE NO ACTION, 
    `name` VARCHAR(255) NOT NULL
);',
        ];

        yield 'postgresql' => [
            DatabaseDialect::POSTGRESQL,
            'CREATE TABLE `books` (
    `id` SERIAL PRIMARY KEY, 
    `author_id` INTEGER  NOT NULL, 
    CONSTRAINT `fk_authors_books_author_id` FOREIGN KEY books(author_id) REFERENCES authors(id) ON DELETE CASCADE ON UPDATE NO ACTION, 
    `name` VARCHAR(255) NOT NULL
);',
        ];

        yield 'sqlite' => [
            DatabaseDialect::SQLITE,
            'CREATE TABLE `books` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT, 
    `author_id` INTEGER  NOT NULL, 
    `name` VARCHAR(255) NOT NULL
);',
        ];
    }
}
