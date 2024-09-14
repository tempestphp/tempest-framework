<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class ParentObject
{
    public string $name;

    public ChildObject $child;
}
