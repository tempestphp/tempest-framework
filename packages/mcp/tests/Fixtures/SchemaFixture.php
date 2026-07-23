<?php

declare(strict_types=1);

namespace Tempest\Mcp\Tests\Fixtures;

use Tempest\Mcp\Description;
use Tempest\Validation\Rules\HasLength;
use Tempest\Validation\Rules\IsBetween;
use Tempest\Validation\Rules\IsIn;

final readonly class SchemaFixture
{
    public function scalars(string $a, int $b, float $c, bool $d, array $e): void {}

    public function optionals(?string $filter, int $limit = 10, ?string $sort = null): void {}

    public function enums(Suit $suit, ?Suit $fallback = null): void {}

    public function injected(string $name, SomeService $service): void {}

    public function constrained(
        #[HasLength(min: 2, max: 10)]
        string $name,
        #[IsBetween(min: 0, max: 100)]
        int $score,
        #[IsIn(['a', 'b'])]
        string $letter,
        #[Description('The described parameter')]
        string $described,
    ): void {}

    public function nothing(SomeService $service): void {}

    public function union(int|string $query): void {}

    public function variadic(string ...$items): void {}
}
