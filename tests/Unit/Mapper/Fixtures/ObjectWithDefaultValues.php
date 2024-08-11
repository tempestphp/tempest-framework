<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Fixtures;

final class ObjectWithDefaultValues
{
    public string $a = 'a';

    public ?string $b = null;
}
