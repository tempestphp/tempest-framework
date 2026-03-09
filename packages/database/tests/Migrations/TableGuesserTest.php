<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\Migrations;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Migrations\TableGuesser;

final class TableGuesserTest extends TestCase
{
    #[Test]
    #[DataProvider('provide_create_patterns')]
    public function it_detects_create_migrations(string $migration, string $expectedTable): void
    {
        $result = TableGuesser::guess($migration);

        $this->assertNotNull($result);
        $this->assertSame($expectedTable, $result->table);
        $this->assertTrue($result->isCreate);
    }

    public static function provide_create_patterns(): array
    {
        return [
            'create_table_with_suffix' => ['create_books_table', 'books'],
            'create_without_suffix' => ['create_books', 'books'],
            'create_users_table' => ['create_users_table', 'users'],
            'create_users' => ['create_users', 'users'],
            'create_multi_word_table' => ['create_blog_posts_table', 'blog_posts'],
            'create_multi_word' => ['create_blog_posts', 'blog_posts'],
        ];
    }

    #[Test]
    #[DataProvider('provide_change_patterns')]
    public function it_detects_change_migrations(string $migration, string $expectedTable): void
    {
        $result = TableGuesser::guess($migration);

        $this->assertNotNull($result);
        $this->assertSame($expectedTable, $result->table);
        $this->assertFalse($result->isCreate);
    }

    public static function provide_change_patterns(): array
    {
        return [
            'add_column_to_table' => ['add_short_summary_to_books_table', 'books'],
            'add_column_to' => ['add_short_summary_to_books', 'books'],
            'remove_column_from_table' => ['remove_name_from_users_table', 'users'],
            'remove_column_from' => ['remove_name_from_users', 'users'],
            'change_column_in_table' => ['change_email_in_accounts_table', 'accounts'],
            'change_column_in' => ['change_email_in_accounts', 'accounts'],
            'rename_to_multi_word' => ['rename_shortsum_to_blog_posts', 'blog_posts'],
        ];
    }

    #[Test]
    #[DataProvider('provide_unmatched_patterns')]
    public function it_returns_null_for_unmatched_patterns(string $migration): void
    {
        $this->assertNull(TableGuesser::guess($migration));
    }

    public static function provide_unmatched_patterns(): array
    {
        return [
            'plain_table_name' => ['books'],
            'plain_multi_word' => ['blog_posts'],
            'random_name' => ['fix_something'],
            'update_without_preposition' => ['update_books'],
        ];
    }
}
