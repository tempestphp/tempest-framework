<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ParentWithChildrenChildObject
{
    public string $name;

    public ParentWithChildrenObject $parent;

    /** @var \Tests\Tempest\Integration\Mapper\Fixtures\ParentWithChildrenObject[] */
    public array $parentCollection;
}
