<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Intl;

use Countable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Intl;
use Tempest\Intl\Locale;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class FunctionsTest extends FrameworkIntegrationTestCase
{
    #[TestWith(['aircraft', 'aircraft', 3])]
    #[TestWith(['migration', 'migrations', 0])]
    #[TestWith(['migration', 'migration', 1])]
    #[TestWith(['Migration', 'Migrations', 2])]
    #[TestWith(['migration', 'migrations', 2])]
    #[TestWith(['migration', 'migrations', [1, 2]])]
    #[Test]
    public function pluralize(string $value, string $expected, int|array|Countable $count): void
    {
        $this->assertEquals($expected, Intl\pluralize($value, $count));
    }

    #[TestWith(['Migrations', 'Migration'])]
    #[TestWith(['migrations', 'migration'])]
    #[Test]
    public function singularize(string $value, string $expected): void
    {
        $this->assertEquals($expected, Intl\singularize($value));
    }

    #[Test]
    public function singularize_last_word(): void
    {
        $this->assertEquals('Multiple Migration', Intl\singularize_last_word('Multiple Migration'));
        $this->assertEquals('Multiple Migration', Intl\singularize_last_word('Multiple Migrations'));
        $this->assertEquals('Multiple Aircraft', Intl\singularize_last_word('Multiple Aircraft'));
    }

    #[Test]
    public function pluralize_last_word(): void
    {
        $this->assertEquals('Multiple Migrations', Intl\pluralize_last_word('Multiple Migration'));
        $this->assertEquals('Multiple Migrations', Intl\pluralize_last_word('Multiple Migrations'));
        $this->assertEquals('Multiple Aircraft', Intl\pluralize_last_word('Multiple Aircraft'));
    }

    #[Test]
    public function current_locale(): void
    {
        $this->assertSame(Locale::ENGLISH_UNITED_STATES, Intl\current_locale());
    }
}
