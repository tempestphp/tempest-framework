<?php

namespace Tempest\Mapper;

/**
 * Provides context information to casters, serializers and mappers.
 */
interface Context
{
    /**
     * A unique name for this context.
     */
    public string $name {
        get;
    }
}
