<?php

declare(strict_types=1);

namespace Tempest\Core\Exceptions;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class ExceptionReporterDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly ExceptionsConfig $config,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(ExceptionReporter::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $className) {
            $this->config->addReporter($className);
        }
    }
}
