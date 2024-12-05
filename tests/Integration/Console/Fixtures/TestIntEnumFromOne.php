<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

// This doesn't start from zero
enum TestIntEnumFromOne: int
{
    case A = 1;
    case B = 2;
    case C = 3;
}
