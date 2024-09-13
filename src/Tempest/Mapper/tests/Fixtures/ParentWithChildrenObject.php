<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Fixtures;

final class ParentWithChildrenObject
{
    public string $name;

    /** @var \Tempest\Mapper\Tests\Fixtures\ParentWithChildrenChildObject[] */
    public array $children = [];
}
