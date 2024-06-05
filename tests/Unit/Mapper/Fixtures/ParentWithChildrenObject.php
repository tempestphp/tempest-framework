<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Fixtures;

final class ParentWithChildrenObject
{
    public string $name;

    /** @var \Tests\Tempest\Unit\Mapper\Fixtures\ParentWithChildrenChildObject[] */
    public array $children = [];
}
