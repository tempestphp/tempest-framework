<?php

namespace Tempest\Mapper;

/**
 * Provides context information to casters, serializers and mappers.
 */
interface Context
{
    /**
     * A unique identifier for this context.
     */
    public string $key {
        get;
    }
}
