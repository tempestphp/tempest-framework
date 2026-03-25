<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ChildObjectWithVirtualProperty
{
    public string $name;

    public ParentObjectWithVirtualChild $parent;

    /** @var \Tests\Tempest\Integration\Mapper\Fixtures\ParentObjectWithVirtualChild[] */
    public array $parentCollection;

    /** @var \Tests\Tempest\Integration\Mapper\Fixtures\ParentObjectWithVirtualChild */
    public ParentObjectWithVirtualChild $virtualParent {
        get => $this->parent;
    }
}
