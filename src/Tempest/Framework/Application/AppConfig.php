<?php

declare(strict_types=1);

namespace Tempest\Framework\Application;

use Tempest\Discovery\DiscoveryDiscovery;

final class AppConfig
{
    public function __construct(
        public string $root,
        public Environment $environment = Environment::LOCAL,
        public bool $enableExceptionHandling = false,
        public bool $discoveryCache = false,

        /** @var class-string[] */
        public array $discoveryClasses = [
            DiscoveryDiscovery::class,
        ],

        /** @var \Tempest\Discovery\DiscoveryLocation[] */
        public array $discoveryLocations = [
            // …,
        ],

        /** @var \Tempest\Framework\Application\ExceptionHandler[] */
        public array $exceptionHandlers = [
            // …,
        ],
    ) {
    }
}
