<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ParentWithChildrenObject
{
    public string $name;

    /** @var \Tests\Tempest\Integration\Mapper\Fixtures\ParentWithChildrenChildObject[] */
    public array $children = [];
}
