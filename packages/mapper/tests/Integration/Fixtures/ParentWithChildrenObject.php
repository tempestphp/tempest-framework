<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

final class ParentWithChildrenObject
{
    public string $name;

    /** @var \Tempest\Mapper\Tests\Integration\Fixtures\ParentWithChildrenChildObject[] */
    public array $children = [];
}
