<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Intl;

use Countable;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Intl;
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
    public function test_pluralize(string $value, string $expected, int|array|Countable $count): void
    {
        $this->assertEquals($expected, Intl\pluralize($value, $count));
    }

    #[TestWith(['Migrations', 'Migration'])]
    #[TestWith(['migrations', 'migration'])]
    public function test_singularize(string $value, string $expected): void
    {
        $this->assertEquals($expected, Intl\singularize($value));
    }

    public function test_singularize_last_word(): void
    {
        $this->assertEquals('Multiple Migration', Intl\singularize_last_word('Multiple Migration'));
        $this->assertEquals('Multiple Migration', Intl\singularize_last_word('Multiple Migrations'));
        $this->assertEquals('Multiple Aircraft', Intl\singularize_last_word('Multiple Aircraft'));
    }

    public function test_pluralize_last_word(): void
    {
        $this->assertEquals('Multiple Migrations', Intl\pluralize_last_word('Multiple Migration'));
        $this->assertEquals('Multiple Migrations', Intl\pluralize_last_word('Multiple Migrations'));
        $this->assertEquals('Multiple Aircraft', Intl\pluralize_last_word('Multiple Aircraft'));
    }
}
