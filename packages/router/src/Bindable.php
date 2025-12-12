<?php

namespace Tempest\Router;

/**
 * Allows resolving the implementing class through a route parameter.
 */
interface Bindable
{
    /**
     * Resolves the implementing class through the given input.
     */
    public static function resolve(string $input): self;
}
