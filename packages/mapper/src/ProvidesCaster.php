<?php

namespace Tempest\Mapper;

/**
 * Implemented on an attribute, this interface specifies that a specific caster must be used to serialize the associated property.
 */
interface ProvidesCaster
{
    /** @var class-string<Caster> */
    public string $caster {
        get;
    }
}
