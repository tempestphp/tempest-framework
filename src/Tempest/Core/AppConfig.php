<?php

declare(strict_types=1);

namespace Tempest\Core;

final class AppConfig
{
    public function __construct(
        public string $root,
        public Environment $environment = Environment::LOCAL,
        public bool $discoveryCache = false,
        public ExceptionHandlerSetup $exceptionHandlerSetup = new GenericExceptionHandlerSetup(),

        /** @var class-string[] */
        public array $discoveryClasses = [
            DiscoveryDiscovery::class,
        ],

        /** @var \Tempest\Core\DiscoveryLocation[] */
        public array $discoveryLocations = [
            // …,
        ],

        /** @var \Tempest\Core\ExceptionHandler[] */
        public array $exceptionHandlers = [
            // …,
        ],
    ) {
    }
}
