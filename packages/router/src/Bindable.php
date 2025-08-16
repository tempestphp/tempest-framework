<?php

namespace Tempest\Router;

interface Bindable
{
    /**
     * @var int|string
     * The value being used to generate URIs when passed into the `\Tempest\Router\uri` function
     */
    public int|string $bindingValue {
        get;
    }

    public static function resolve(string $input): self;
}
