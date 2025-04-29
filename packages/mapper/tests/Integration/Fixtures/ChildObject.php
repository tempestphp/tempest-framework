<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

final class ChildObject
{
    public string $name;

    public ParentObject $parent;

    /** @var \Tempest\Mapper\Tests\Integration\Fixtures\ParentObject[] */
    public array $parentCollection;
}
