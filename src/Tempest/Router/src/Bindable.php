<?php

namespace Tempest\Router;

interface Bindable
{
    public static function resolve(string $input): static;
}
