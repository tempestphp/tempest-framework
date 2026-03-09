<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

interface TypeScriptGenerationConfig
{
    /**
     * The writer class to use for output.
     *
     * @var class-string<TypeScriptWriter>
     */
    public string $writer {
        get;
    }

    /**
     * The list of source classes to generate types for.
     *
     * @var array<class-string>
     */
    public array $sources {
        get;
        set;
    }

    /**
     * The list of type resolvers for property-level type mapping.
     *
     * @var array<class-string<TypeResolver>>
     */
    public array $resolvers {
        get;
        set;
    }
}
