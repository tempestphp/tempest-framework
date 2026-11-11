<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Reflection\Fixtures\Plain;

final class PlainModels
{
    public array $noDocblock;

    /** @var int[] */
    public array $integers;

    /** @var list<string> */
    public array $strings;
}
