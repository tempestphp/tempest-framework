<?php

namespace Tempest\Container;

trait HasInstance
{
    private static self $instance;

    public static function instance(): self
    {
        return self::$instance;
    }

    public static function setInstance(self $instance): void
    {
        self::$instance = $instance;
    }
}
