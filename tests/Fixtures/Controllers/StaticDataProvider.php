<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Generator;
use Tempest\Http\DataProvider;

final readonly class StaticDataProvider implements DataProvider
{
    public function provide(): Generator
    {
        yield ['foo' => 'a', 'bar' => 'b'];
        yield ['foo' => 'c', 'bar' => 'd'];
    }
}
