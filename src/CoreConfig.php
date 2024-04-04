<?php

namespace Tempest;

use Tempest\Discovery\DiscoveryDiscovery;

final class CoreConfig
{
    public function __construct(
        public string $root,

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

        /** @var \Tempest\ExceptionHandler[] */
        public array $exceptionHandlers = [
            // …,
        ],
    ) {}
}