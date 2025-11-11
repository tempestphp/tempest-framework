<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

class ObjectWithStringOrMixedArray
{
    /** @var string|mixed[] */
    public string|array $items;
}
