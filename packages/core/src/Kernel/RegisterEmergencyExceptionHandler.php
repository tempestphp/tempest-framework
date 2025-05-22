<?php

namespace Tempest\Core\Kernel;

use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

final readonly class RegisterEmergencyExceptionHandler
{
    public function register(): void
    {
        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        $whoops->register();
    }
}
