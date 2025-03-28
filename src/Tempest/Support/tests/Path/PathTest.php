<?php

namespace Tempest\Support\Tests\Path;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Path\Path;

final class PathTest extends TestCase
{
    #[TestWith([['FunctionsTest.php', 'PathTest.php'], __DIR__, '*.php'])]
    public function test_glob(array $expected, string $input, string $glob): void
    {
        $this->assertSame(
            $expected,
            new Path($input)
                ->glob($glob)
                ->map(fn (string $path) => basename($path))
                ->toArray(),
        );
    }
}
