<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Fixtures;

final class ParentWithChildrenChildObject
{
    public string $name;
    public ParentWithChildrenObject $parentWithChildrenObject;
    /** @var \Tests\Tempest\Unit\Mapper\Fixtures\ParentWithChildrenObject[] */
    public array $parentWithChildrenObjects;
}
