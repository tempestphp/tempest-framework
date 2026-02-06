<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Core\Priority;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;
use Tempest\Router\Exceptions\ExceptionRenderer;

final class ExceptionRendererDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly RouteConfig $routeConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(ExceptionRenderer::class)) {
            return;
        }

        $priority = $class->getAttribute(Priority::class)->priority ?? Priority::NORMAL;

        $this->discoveryItems->add($location, [$class->getName(), $priority]);
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$className, $priority]) {
            $this->routeConfig->addExceptionRenderer($className, $priority);
        }
    }
}
