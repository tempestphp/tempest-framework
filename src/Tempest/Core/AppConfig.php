<?php

declare(strict_types=1);

namespace Tempest\Core;

final class AppConfig
{
    public function __construct(
        public Environment $environment = Environment::LOCAL,
        public ExceptionHandlerSetup $exceptionHandlerSetup = new GenericExceptionHandlerSetup(),

        /** @var \Tempest\Core\ExceptionHandler[] */
        public array $exceptionHandlers = [
            // …,
        ],
    ) {
    }
}
