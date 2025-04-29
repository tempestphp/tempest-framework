<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

final class ParentObject
{
    public string $name;

    public ChildObject $child;
}
