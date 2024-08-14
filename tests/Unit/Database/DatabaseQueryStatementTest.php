<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Database;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\OnDelete;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\VarcharStatement;

/**
 * @internal
 * @small
 */
final class DatabaseQueryStatementTest extends TestCase
{
    #[Test]
    #[DataProvider('provide_create_table_database_drivers')]
    public function it_can_create_a_table(DatabaseDialect $dialect, string $validSql): void
    {
        $statement = new CreateTableStatement('Migration', [
            new PrimaryKeyStatement(),
            new VarcharStatement('name'),
        ]);

        $this->assertSame($validSql, $statement->compile($dialect));
    }

    public static function provide_create_table_database_drivers(): Generator
    {
        yield 'mysql' => [
            DatabaseDialect::MYSQL,
            'CREATE TABLE Migration (id INTEGER PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255) NOT NULL);',
        ];

        yield 'postgresql' => [
            DatabaseDialect::POSTGRESQL,
            'CREATE TABLE Migration (id SERIAL PRIMARY KEY, name VARCHAR(255) NOT NULL);',
        ];

        yield 'sqlite' => [
            DatabaseDialect::SQLITE,
            'CREATE TABLE Migration (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255) NOT NULL);',
        ];
    }

    #[Test]
    #[DataProvider('provide_fk_create_table_database_drivers')]
    public function it_can_create_a_foreign_key_constraint(DatabaseDialect $dialect, string $validSql): void
    {
        $statement = (new CreateTableStatement('Book'))
            ->primary()
            ->belongsTo('Book.author_id', 'Author.id', OnDelete::CASCADE)
            ->varchar('name')
            ->compile($dialect);

        $this->assertSame($validSql, $statement);
    }

    public static function provide_fk_create_table_database_drivers(): Generator
    {
        yield 'mysql' => [
            DatabaseDialect::MYSQL,
            'CREATE TABLE Book (id INTEGER PRIMARY KEY AUTO_INCREMENT, author_id INTEGER UNSIGNED NOT NULL, CONSTRAINT fk_author_book FOREIGN KEY Book(author_id) REFERENCES Author(id) ON DELETE CASCADE ON UPDATE NO ACTION, name VARCHAR(255) NOT NULL);',
        ];

        yield 'postgresql' => [
            DatabaseDialect::POSTGRESQL,
            'CREATE TABLE Book (id SERIAL PRIMARY KEY, author_id INTEGER UNSIGNED NOT NULL, CONSTRAINT fk_author_book FOREIGN KEY Book(author_id) REFERENCES Author(id) ON DELETE CASCADE ON UPDATE NO ACTION, name VARCHAR(255) NOT NULL);',
        ];

        yield 'sqlite' => [
            DatabaseDialect::SQLITE,
            'CREATE TABLE Book (id INTEGER PRIMARY KEY AUTOINCREMENT, author_id INTEGER UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL);',
        ];
    }

    #[Test]
    #[DataProvider('provide_alter_table_syntax')]
    public function it_can_create_an_alter_table_add_statement(DatabaseDialect $dialect, string $operation, string $validSql): void
    {
        $query = (new AlterTableStatement('Author'))
            ->$operation(new VarcharStatement('name'))
            ->compile($dialect);

        $this->assertSame($validSql, $query);
    }

    public static function provide_alter_table_syntax(): Generator
    {
        yield 'mysql add statement' => [
            DatabaseDialect::MYSQL,
            'add',
            'ALTER TABLE Author ADD name VARCHAR(255) NOT NULL;',
        ];

        yield 'postgresql add statement' => [
            DatabaseDialect::POSTGRESQL,
            'add',
            'ALTER TABLE Author ADD COLUMN name VARCHAR(255) NOT NULL;',
        ];

        yield 'sqlite add statement' => [
            DatabaseDialect::SQLITE,
            'add',
            'ALTER TABLE Author ADD COLUMN name VARCHAR(255) NOT NULL;',
        ];

        yield 'mysql delete statement' => [
            DatabaseDialect::MYSQL,
            'delete',
            'ALTER TABLE Author DELETE name VARCHAR(255) NOT NULL;',
        ];

        yield 'postgresql delete statement' => [
            DatabaseDialect::POSTGRESQL,
            'delete',
            'ALTER TABLE Author DELETE COLUMN name VARCHAR(255) NOT NULL;',
        ];

        yield 'sqlite delete statement' => [
            DatabaseDialect::SQLITE,
            'delete',
            'ALTER TABLE Author DELETE COLUMN name VARCHAR(255) NOT NULL;',
        ];
    }
}
