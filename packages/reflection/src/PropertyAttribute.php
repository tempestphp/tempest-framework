<?php

namespace Tempest\Reflection;

interface PropertyAttribute
{
    public PropertyReflector $property {
        set;
        get;
    }
}
