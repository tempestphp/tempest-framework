<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Static\Fixtures;

use Generator;
use Tempest\Router\DataProvider;

final readonly class StaticDataProvider implements DataProvider
{
    public function provide(): Generator
    {
        yield ['foo' => 'a', 'bar' => 'b'];
        yield ['foo' => 'c', 'bar' => 'd'];
    }
}
