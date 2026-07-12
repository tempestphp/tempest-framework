<?php

namespace Tempest\Container;

use UnitEnum;

interface HasTag
{
    public string|UnitEnum|null $tag { get; }
}
