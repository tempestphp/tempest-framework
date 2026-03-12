<?php

namespace Tempest\Mapper;

/**
 * Implemented on an attribute, this interface specifies that a specific serializer must be used to serialize the associated property.
 */
interface ProvidesSerializer
{
    /** @var class-string<Serializer> */
    public string $serializer {
        get;
    }
}
