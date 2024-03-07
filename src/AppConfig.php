<?php

declare(strict_types=1);

namespace Tempest;

use Tempest\Application\Environment;
use Tempest\Discovery\DiscoveryDiscovery;

final class AppConfig
{
    public function __construct(
        public string $root,
        public Environment $environment = Environment::LOCAL,

        public bool $discoveryCache = false,

        /** @var \Tempest\Discovery\DiscoveryLocation[] */
        public array $discoveryLocations = [],

        /** @var class-string[] */
        public array $discoveryClasses = [
            DiscoveryDiscovery::class,
        ],

        /** @var \Tempest\Exceptions\ExceptionHandler[] */
        public array $exceptionHandlers = [],
        public bool $enableExceptionHandling = true,
    ) {
    }
}
