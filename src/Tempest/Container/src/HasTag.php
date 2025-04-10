<?php

namespace Tempest\Container;

use UnitEnum;

interface HasTag
{
    public null|string|UnitEnum $tag {
        get;
    }
}
