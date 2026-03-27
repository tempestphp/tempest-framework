<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ParentObjectWithGetterChild
{
    public string $name;

    public ?ChildObjectWithGetterOnly $child = null;

    /** @var \Tests\Tempest\Integration\Mapper\Fixtures\ChildObjectWithGetterOnly[] */
    public array $children = [];
}
