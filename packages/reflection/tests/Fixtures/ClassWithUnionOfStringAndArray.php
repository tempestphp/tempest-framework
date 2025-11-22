<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures;

class ClassWithUnionOfStringAndArray
{
    /**
     * @var string|string[]|null
     */
    public string|array|null $items;
}
