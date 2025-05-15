<?php

namespace Tempest\HttpApi;

use Tempest\Reflection\ClassReflector;

trait HasResourceRecord
{
    public static function getResourceRecord(): string
    {
        $resourceClassReflector = new ClassReflector(static::class);

        if ($resourceRecord = $resourceClassReflector->getAttribute(ResourceRecord::class)) {
            return $resourceRecord->resourceRecord;
        };

        return static::class;
    }
}
