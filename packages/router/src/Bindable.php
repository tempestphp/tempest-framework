<?php

namespace Tempest\Router;

interface Bindable
{
    public int|string $bindingValue {
        get;
    }

    public static function resolve(string $input): self;
}
