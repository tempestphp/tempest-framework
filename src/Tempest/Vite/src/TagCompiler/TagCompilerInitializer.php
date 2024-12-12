<?php

declare(strict_types=1);

namespace Tempest\Vite\TagCompiler;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final class TagCompilerInitializer implements Initializer
{
    public function initialize(Container $container): TagCompiler
    {
        return $container->get(GenericTagCompiler::class);
    }
}
