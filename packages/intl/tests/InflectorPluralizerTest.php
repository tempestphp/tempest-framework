<?php

declare(strict_types=1);

namespace Tempest\Intl\Tests;

use Countable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Intl\Pluralizer\InflectorPluralizer;

/**
 * @internal
 */
final class InflectorPluralizerTest extends TestCase
{
    #[TestWith(['migration', 'migrations', 0])]
    #[TestWith(['migration', 'migration', 1])]
    #[TestWith(['Migration', 'Migrations', 2])]
    #[TestWith(['migration', 'migrations', 2])]
    #[TestWith(['migration', 'migrations', [1, 2]])]
    #[TestWith(['category', 'categories', 2])]
    #[TestWith(['status', 'statuses', 2])]
    #[TestWith(['child', 'children', 2])]
    #[Test]
    public function that_pluralizer_pluralizes(string $value, string $expected, int|array|Countable $count): void
    {
        $pluralizer = new InflectorPluralizer();

        $this->assertEquals($expected, $pluralizer->pluralize($value, $count));
    }

    #[TestWith(['Migrations', 'Migration'])]
    #[TestWith(['migrations', 'migration'])]
    #[TestWith(['categories', 'category'])]
    #[TestWith(['statuses', 'status'])]
    #[TestWith(['children', 'child'])]
    #[Test]
    public function that_pluralizer_singularizes(string $value, string $expected): void
    {
        $pluralizer = new InflectorPluralizer();

        $this->assertEquals($expected, $pluralizer->singularize($value));
    }

    #[TestWith(['bookAuthors', 'bookAuthor'])]
    #[TestWith(['BookAuthors', 'BookAuthor'])]
    #[TestWith(['book_authors', 'book_author'])]
    #[TestWith(['product_metadata', 'product_metadata'])]
    #[TestWith(['user_book_categories', 'user_book_category'])]
    #[TestWith(['authors', 'author'])]
    #[TestWith(['', ''])]
    #[TestWith(['BOOK_AUTHORS', 'BOOK_AUTHOR'])]
    #[TestWith(['USER_STATUSES', 'USER_STATUS'])]
    #[TestWith(['BOOK_CATEGORIES', 'BOOK_CATEGORY'])]
    #[TestWith(['parseHTMLElements', 'parseHTMLElement'])]
    #[TestWith(['HTMLElements', 'HTMLElement'])]
    #[TestWith(['getHTTPSResponses', 'getHTTPSResponse'])]
    #[TestWith(['_authors', '_author'])]
    #[TestWith(['authors_', 'author_'])]
    #[TestWith(['authors__', 'author__'])]
    #[TestWith(['book__authors', 'book__author'])]
    #[TestWith(['_', '_'])]
    #[TestWith(['__', '__'])]
    #[TestWith(['Multiple Aircraft', 'Multiple Aircraft'])]
    #[TestWith(['multiple aircraft', 'multiple aircraft'])]
    #[TestWith(['small dogs', 'small dog'])]
    #[Test]
    public function singularize_last_word(string $value, string $expected): void
    {
        $pluralizer = new InflectorPluralizer();

        $this->assertEquals($expected, $pluralizer->singularizeLastWord($value));
    }

    #[TestWith(['bookAuthor', 'bookAuthors'])]
    #[TestWith(['BookAuthor', 'BookAuthors'])]
    #[TestWith(['book_author', 'book_authors'])]
    #[TestWith(['product_metadata', 'product_metadata'])]
    #[TestWith(['user_book_category', 'user_book_categories'])]
    #[TestWith(['author', 'authors'])]
    #[TestWith(['', ''])]
    #[TestWith(['BOOK_AUTHOR', 'BOOK_AUTHORS'])]
    #[TestWith(['USER_STATUS', 'USER_STATUSES'])]
    #[TestWith(['BOOK_CATEGORY', 'BOOK_CATEGORIES'])]
    #[TestWith(['parseHTMLElement', 'parseHTMLElements'])]
    #[TestWith(['HTMLElement', 'HTMLElements'])]
    #[TestWith(['getHTTPSResponse', 'getHTTPSResponses'])]
    #[TestWith(['_author', '_authors'])]
    #[TestWith(['author_', 'authors_'])]
    #[TestWith(['author__', 'authors__'])]
    #[TestWith(['book__author', 'book__authors'])]
    #[TestWith(['_', '_'])]
    #[TestWith(['__', '__'])]
    #[TestWith(['Multiple Aircraft', 'Multiple Aircraft'])]
    #[TestWith(['multiple aircraft', 'multiple aircraft'])]
    #[TestWith(['small dog', 'small dogs'])]
    #[Test]
    public function pluralize_last_word(string $value, string $expected): void
    {
        $pluralizer = new InflectorPluralizer();

        $this->assertEquals($expected, $pluralizer->pluralizeLastWord($value));
    }
}
