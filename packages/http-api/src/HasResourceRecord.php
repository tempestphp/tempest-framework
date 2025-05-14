<?php

namespace Tempest\HttpApi;

trait HasResourceRecord
{
    public static function getResourceRecord(): string
    {
        if (property_exists(static::class, 'resourceRecord')) {
            return static::$resourceRecord;
        }

        return static::class;
    }
}
