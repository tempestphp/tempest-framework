<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ParentObjectWithVirtualChild
{
    public string $name;

    public ?ChildObjectWithVirtualProperty $child = null;

    /** @var \Tests\Tempest\Integration\Mapper\Fixtures\ChildObjectWithVirtualProperty[] */
    public array $children = [];
}
