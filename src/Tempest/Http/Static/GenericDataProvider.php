<?php

declare(strict_types=1);

namespace Tempest\Http\Static;

use Generator;
use Tempest\Http\DataProvider;

final readonly class GenericDataProvider implements DataProvider
{
    public function provide(): Generator
    {
        yield [];
    }
}
