<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Pluralizer;

use Countable;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Pluralizer\InflectorPluralizer;

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
    public function test_that_pluralizer_pluralizes(string $value, string $expected, int|array|Countable $count): void
    {
        $pluralizer = new InflectorPluralizer();

        $this->assertEquals($expected, $pluralizer->pluralize($value, $count));
    }

    #[TestWith(['Migrations', 'Migration'])]
    #[TestWith(['migrations', 'migration'])]
    public function test_that_pluralizer_singularizes(string $value, string $expected): void
    {
        $pluralizer = new InflectorPluralizer();

        $this->assertEquals($expected, $pluralizer->singularize($value));
    }
}
