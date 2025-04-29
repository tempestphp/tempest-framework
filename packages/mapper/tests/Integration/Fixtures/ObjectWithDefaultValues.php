<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

final class ObjectWithDefaultValues
{
    public string $a = 'a';

    public ?string $b = null;
}
