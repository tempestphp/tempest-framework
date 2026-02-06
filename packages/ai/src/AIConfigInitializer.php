<?php

declare(strict_types=1);

namespace Tempest\AI;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class AIConfigInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): AIConfig
    {
        return new AIConfig();
    }
}
