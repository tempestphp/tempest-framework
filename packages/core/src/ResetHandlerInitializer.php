<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class ResetHandlerInitializer implements Initializer
{
    public function initialize(Container $container): ResetHandler
    {
        return $container->get(GenericResetHandler::class);
    }
}
