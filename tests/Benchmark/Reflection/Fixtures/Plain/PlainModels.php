<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Reflection\Fixtures\Plain;

final class PlainModels
{
    public array $one;

    /** @var int[] */
    public array $two;

    /** @var list<string> */
    public array $three;
}
