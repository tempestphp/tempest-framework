<?php

declare(strict_types=1);

namespace Tempest\Application;

interface BootstrapsKernel
{
    public function bootstrap(Kernel $kernel): void;
}
