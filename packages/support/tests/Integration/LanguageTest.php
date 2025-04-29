<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Integration;

use Countable;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Drift\FrameworkIntegrationTestCase;

use function Tempest\Support\Language\pluralize;
use function Tempest\Support\Language\singularize;

/**
 * @internal
 */
final class LanguageTest extends FrameworkIntegrationTestCase
{
    #[TestWith(['migration', 'migrations', 0])]
    #[TestWith(['migration', 'migration', 1])]
    #[TestWith(['Migration', 'Migrations', 2])]
    #[TestWith(['migration', 'migrations', 2])]
    #[TestWith(['migration', 'migrations', [1, 2]])]
    public function test_that_pluralize_pluralizes(string $value, string $expected, int|array|Countable $count): void
    {
        $this->assertEquals($expected, pluralize($value, $count));
    }

    #[TestWith(['Migrations', 'Migration'])]
    #[TestWith(['migrations', 'migration'])]
    public function test_that_pluralizer_singularizes(string $value, string $expected): void
    {
        $this->assertEquals($expected, singularize($value));
    }
}
