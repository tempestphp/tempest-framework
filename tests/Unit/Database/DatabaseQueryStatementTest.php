<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Database;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\DatabaseDriver;
use Tempest\Database\Drivers\MySqlDriver;
use Tempest\Database\Drivers\PostgreSqlDriver;
use Tempest\Database\Drivers\SQLiteDriver;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\OnDelete;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\RawStatement;

/**
 * @internal
 * @small
 */
final class DatabaseQueryStatementTest extends TestCase
{
    #[Test]
    #[DataProvider('provide_create_table_database_drivers')]
    public function it_can_create_a_table(DatabaseDriver $driver, string $validSql): void
    {
        $statement = (new CreateTableStatement('Migration', [
            new PrimaryKeyStatement(),
            new RawStatement('`name` VARCHAR(255) NOT NULL'),
        ]))->compile($driver->dialect());

        $this->assertSame($validSql, $statement);
    }

    public static function provide_create_table_database_drivers(): Generator
    {
        yield 'mysql' => [
            new MySqlDriver(),
            'CREATE TABLE `Migration` (`id` INTEGER PRIMARY KEY AUTO_INCREMENT, `name` VARCHAR(255) NOT NULL);',
        ];

        yield 'postgresql' => [
            new PostgreSqlDriver(),
            'CREATE TABLE `Migration` (`id` SERIAL PRIMARY KEY, `name` VARCHAR(255) NOT NULL);',
        ];

        yield 'sqlite' => [
            new SQLiteDriver(),
            'CREATE TABLE `Migration` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` VARCHAR(255) NOT NULL);',
        ];
    }

    #[Test]
    #[DataProvider('provide_fk_create_table_database_drivers')]
    public function it_can_create_a_foreign_key_constraint(DatabaseDriver $driver, string $validSql): void
    {
        $statement = (new CreateTableStatement('Book'))
            ->primary()
            ->belongsTo('Book.author_id', 'Author.id', OnDelete::CASCADE)
            ->varchar('name')
            ->compile($driver->dialect());

        $this->assertSame($validSql, $statement);
    }

    public static function provide_fk_create_table_database_drivers(): Generator
    {
        yield 'mysql' => [
            new MySqlDriver(),
            'CREATE TABLE `Book` (`id` INTEGER PRIMARY KEY AUTO_INCREMENT, `author_id` INTEGER UNSIGNED NOT NULL, CONSTRAINT fk_author_book FOREIGN KEY Book(author_id) REFERENCES Author(id) ON DELETE CASCADE ON UPDATE NO ACTION, `name` VARCHAR(255) NOT NULL);',
        ];

        yield 'postgresql' => [
            new PostgreSqlDriver(),
            'CREATE TABLE `Book` (`id` SERIAL PRIMARY KEY, `author_id` INTEGER UNSIGNED NOT NULL, CONSTRAINT fk_author_book FOREIGN KEY Book(author_id) REFERENCES Author(id) ON DELETE CASCADE ON UPDATE NO ACTION, `name` VARCHAR(255) NOT NULL);',
        ];

        yield 'sqlite' => [
            new SQLiteDriver(),
            'CREATE TABLE `Book` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `author_id` INTEGER UNSIGNED NOT NULL, `name` VARCHAR(255) NOT NULL);',
        ];
    }

    public static function provide_database_driver(): Generator
    {
        yield 'mysql' => [
            new MySqlDriver(),
        ];

        yield 'postgresql' => [
            new PostgreSqlDriver(),
        ];

        yield 'sqlite' => [
            new SQLiteDriver(),
        ];
    }

    #[Test]
    #[DataProvider('provide_alter_table_syntax')]
    public function it_can_create_an_alter_table_add_statement(DatabaseDriver $driver, string $operation, string $validSql): void
    {
        $this->markTestIncomplete('TODO: add alter table statement');
        //        $statement = $driver->dialect()->createQueryStatement('Author')
        //            ->alterTable($operation)
        //            ->createColumn('name', 'VARCHAR(255)');
        //
        //        $this->assertSame($validSql, (string) $statement);
    }

    public static function provide_alter_table_syntax(): Generator
    {
        yield 'mysql add statement' => [
            new MySqlDriver(),
            'ADD',
            'ALTER TABLE `Author` ADD `name` VARCHAR(255) NOT NULL;',
        ];

        yield 'postgresql add statement' => [
            new PostgreSqlDriver(),
            'ADD',
            'ALTER TABLE `Author` ADD COLUMN `name` VARCHAR(255) NOT NULL;',
        ];

        yield 'sqlite add statement' => [
            new SQLiteDriver(),
            'ADD',
            'ALTER TABLE `Author` ADD COLUMN `name` VARCHAR(255) NOT NULL;',
        ];

        yield 'mysql delete statement' => [
            new MySqlDriver(),
            'DELETE',
            'ALTER TABLE `Author` DELETE `name` VARCHAR(255) NOT NULL;',
        ];

        yield 'postgresql delete statement' => [
            new PostgreSqlDriver(),
            'DELETE',
            'ALTER TABLE `Author` DELETE COLUMN `name` VARCHAR(255) NOT NULL;',
        ];

        yield 'sqlite delete statement' => [
            new SQLiteDriver(),
            'DELETE',
            'ALTER TABLE `Author` DELETE COLUMN `name` VARCHAR(255) NOT NULL;',
        ];
    }
}
