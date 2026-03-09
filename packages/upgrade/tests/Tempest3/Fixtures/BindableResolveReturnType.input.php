<?php

use Tempest\Router\Bindable;

final class UserBinding implements Bindable
{
    public static function resolve(string $input): self
    {
        return new self();
    }
}
