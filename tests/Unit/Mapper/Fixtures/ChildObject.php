<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Fixtures;

final class ChildObject
{
    public string $name;

    public ParentObject $parent;

    /** @var \Tests\Tempest\Unit\Mapper\Fixtures\ParentObject[] */
    public array $parentCollection;
}
