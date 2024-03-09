<?php

declare(strict_types=1);

namespace Tempest;

use Tempest\Application\Environment;
use Tempest\Discovery\DiscoveryDiscovery;
use Tempest\Exceptions\ExceptionHandler;

final class AppConfig
{
    public Environment $environment = Environment::LOCAL;

    public bool $discoveryCache = false;

    /** @var \Tempest\Discovery\DiscoveryLocation[] */
    public array $discoveryLocations = [];

    /** @var class-string[] */
    public array $discoveryClasses = [
        DiscoveryDiscovery::class,
    ];

    /** @var \Tempest\Exceptions\ExceptionHandler[] */
    public array $exceptionHandlers = [];

    public bool $enableExceptionHandling = true;

    public function __construct(
        ?Environment $environment = null,
        bool $discoveryCache = false,
        /** @var ExceptionHandler[] $exceptionHandlers */
        array $exceptionHandlers = [],
        bool $enableExceptionHandling = true
    ) {
        $this->environment = $environment ?? Environment::tryFrom(env('environment', 'local'));
        $this->discoveryCache = $discoveryCache;
        $this->exceptionHandlers = $exceptionHandlers;
        $this->enableExceptionHandling = $enableExceptionHandling;
    }
}
