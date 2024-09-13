<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Fixtures;

final class ParentWithChildrenChildObject
{
    public string $name;

    public ParentWithChildrenObject $parent;

    /** @var \Tempest\Mapper\Tests\Fixtures\ParentWithChildrenObject[] */
    public array $parentCollection;
}
