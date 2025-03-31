<?php

namespace Tempest\Container;

use UnitEnum;

interface TaggedConfig
{
    public null|UnitEnum|string $tag {
        get;
    }
}
