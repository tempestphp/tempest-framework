<?php

namespace Tempest\Generation\TypeScript;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Generation\TypeScript\StructureResolvers\ClassStructureResolver;
use Tempest\Generation\TypeScript\StructureResolvers\EnumStructureResolver;

final class TypeScriptGeneratorInitializer implements Initializer
{
    public function initialize(Container $container): TypeScriptGenerator
    {
        return new GenericTypeScriptGenerator(
            config: $container->get(TypeScriptGenerationConfig::class),
            classResolver: $container->get(ClassStructureResolver::class),
            enumResolver: $container->get(EnumStructureResolver::class),
        );
    }
}
