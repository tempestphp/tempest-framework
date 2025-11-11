<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ObjectWithUnionArray
{
    /** @var string|string[]|null */
    public string|array|null $items;
}
