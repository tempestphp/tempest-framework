<?php

namespace Tempest\Database\Exceptions;

use Exception;
use Tempest\Support\Arr\ImmutableArray;

final class InsertColumnsMismatched extends Exception
{
    public function __construct(
        ImmutableArray $expected,
        ImmutableArray $actual,
    ) {
        $expected = $expected->map(fn ($column) => "`{$column}`")->join();
        $actual = $actual->map(fn ($column) => "`{$column}`")->join();

        parent::__construct("Expected columns {$expected}; but got {$actual}");
    }
}
