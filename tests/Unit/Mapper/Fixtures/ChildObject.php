<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Fixtures;

final readonly class ChildObject
{
    public string $name;
    public ParentObject $parentObject;
    /** @var \Tests\Tempest\Unit\Mapper\Fixtures\ParentObject[] */
    public array $parentObjects;
}
