<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;

interface ResetHandler
{
    public function reset(Container $container): void;
}
