<?php

declare(strict_types=1);

namespace Tempest\Core;

interface ExceptionHandlerSetup
{
    public function setup(AppConfig $appConfig): void;
}
