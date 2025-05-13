<?php

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\JoinStatement;

final class JoinStatementTest extends TestCase
{
    public function test_inner_join_is_added_when_needed(): void
    {
        $this->assertSame(
            'INNER JOIN authors on authors.id = books.author_id',
            new JoinStatement('authors on authors.id = books.author_id')->compile(DatabaseDialect::MYSQL)
        );

        $this->assertSame(
            'inner join authors on authors.id = books.author_id',
            new JoinStatement('inner join authors on authors.id = books.author_id')->compile(DatabaseDialect::MYSQL)
        );

        $this->assertSame(
            'INNER JOIN authors on authors.id = books.author_id',
            new JoinStatement('INNER JOIN authors on authors.id = books.author_id')->compile(DatabaseDialect::MYSQL)
        );

        $this->assertSame(
            'LEFT JOIN authors on authors.id = books.author_id',
            new JoinStatement('LEFT JOIN authors on authors.id = books.author_id')->compile(DatabaseDialect::MYSQL)
        );

        $this->assertSame(
            'RIGHT JOIN authors on authors.id = books.author_id',
            new JoinStatement('RIGHT JOIN authors on authors.id = books.author_id')->compile(DatabaseDialect::MYSQL)
        );

        $this->assertSame(
            'FULL JOIN authors on authors.id = books.author_id',
            new JoinStatement('FULL JOIN authors on authors.id = books.author_id')->compile(DatabaseDialect::MYSQL)
        );

        $this->assertSame(
            'JOIN authors on authors.id = books.author_id',
            new JoinStatement('JOIN authors on authors.id = books.author_id')->compile(DatabaseDialect::MYSQL)
        );

        $this->assertSame(
            'FULL OUTER JOIN authors on authors.id = books.author_id',
            new JoinStatement('FULL OUTER JOIN authors on authors.id = books.author_id')->compile(DatabaseDialect::MYSQL)
        );

        $this->assertSame(
            'FULL JOIN authors on authors.id = books.author_id',
            new JoinStatement('FULL JOIN authors on authors.id = books.author_id')->compile(DatabaseDialect::MYSQL)
        );

        $this->assertSame(
            'SELF JOIN authors on authors.id = books.author_id',
            new JoinStatement('SELF JOIN authors on authors.id = books.author_id')->compile(DatabaseDialect::MYSQL)
        );
    }
}