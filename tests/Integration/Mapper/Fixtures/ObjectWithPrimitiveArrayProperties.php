<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ObjectWithPrimitiveArrayProperties
{
    /** @var string[] */
    public array $strings;

    /** @var int[] */
    public array $ints;

    /** @var float[] */
    public array $floats;

    /** @var bool[] */
    public array $bools;
}
