<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ChildObjectWithGetterOnly
{
    public string $name;

    public ParentObjectWithGetterChild $parent;

    /** @var \Tests\Tempest\Integration\Mapper\Fixtures\ParentObjectWithGetterChild[] */
    public array $parentCollection;

    public string $upperName {
        get => strtoupper($this->name);
    }
}
