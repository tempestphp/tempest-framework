<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ChildObject
{
    public string $name;

    public ParentObject $parent;

    /** @var \Tests\Tempest\Integration\Mapper\Fixtures\ParentObject[] */
    public array $parentCollection;
}
