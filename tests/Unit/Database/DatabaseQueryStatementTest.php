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
use Tempest\Database\QueryStatement;

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
        $statement = QueryStatement::new($driver, 'Migration')
            ->createTable()
            ->primary()
            ->statement('name VARCHAR(255) NOT NULL');

        $this->assertSame($validSql, (string) $statement);
    }

    public static function provide_create_table_database_drivers(): Generator
    {
        yield 'mysql' => [
            new MySqlDriver(),
            'CREATE TABLE Migration (id INTEGER PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255) NOT NULL);',
        ];

        yield 'postgresql' => [
            new PostgreSqlDriver(),
            'CREATE TABLE Migration (id SERIAL PRIMARY KEY, name VARCHAR(255) NOT NULL);',
        ];

        yield 'sqlite' => [
            new SQLiteDriver(),
            'CREATE TABLE Migration (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255) NOT NULL);',
        ];
    }

    #[Test]
    #[DataProvider('provide_fk_create_table_database_drivers')]
    public function it_can_create_a_foreign_key_constraint(DatabaseDriver $driver, string $validSql): void
    {
        $statement = QueryStatement::new($driver, 'Book')
            ->createTable()
            ->primary()
            ->statement('author_id INTEGER UNSIGNED NOT NULL')
            ->createForeignKey('author_id', 'Author')
            ->statement('name VARCHAR(255) NOT NULL');

        $this->assertSame($validSql, (string) $statement);
    }

    public static function provide_fk_create_table_database_drivers(): Generator
    {
        yield 'mysql' => [
            new MySqlDriver(),
            'CREATE TABLE Book (id INTEGER PRIMARY KEY AUTO_INCREMENT, author_id INTEGER UNSIGNED NOT NULL, CONSTRAINT fk_author_book FOREIGN KEY (author_id) REFERENCES Author(id) ON DELETE CASCADE ON UPDATE NO ACTION, name VARCHAR(255) NOT NULL);',
        ];

        yield 'postgresql' => [
            new PostgreSqlDriver(),
            'CREATE TABLE Book (id SERIAL PRIMARY KEY, author_id INTEGER UNSIGNED NOT NULL, CONSTRAINT fk_author_book FOREIGN KEY (author_id) REFERENCES Author(id) ON DELETE CASCADE ON UPDATE NO ACTION, name VARCHAR(255) NOT NULL);',
        ];

        yield 'sqlite' => [
            new SQLiteDriver(),
            'CREATE TABLE Book (id INTEGER PRIMARY KEY AUTOINCREMENT, author_id INTEGER UNSIGNED NOT NULL, FOREIGN KEY (author_id) REFERENCES Author (id) ON DELETE CASCADE ON UPDATE NO ACTION, name VARCHAR(255) NOT NULL);',
        ];
    }
}
