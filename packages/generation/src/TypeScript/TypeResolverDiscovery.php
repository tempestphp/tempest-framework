<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

use Tempest\Core\Priority;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class TypeResolverDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private TypeScriptGenerationConfig $config,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(TypeResolver::class)) {
            $this->discoveryItems->add($location, [
                $class->getName(),
                $class->getAttribute(Priority::class)->priority ?? Priority::NORMAL,
            ]);
        }
    }

    public function apply(): void
    {
        // Collect all resolvers with their priorities
        $resolvers = [];
        foreach ($this->discoveryItems as [$className, $priority]) {
            $resolvers[] = ['class' => $className, 'priority' => $priority];
        }

        // Sort by priority (lower values first - framework uses ascending priority)
        usort($resolvers, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        // Extract just the class names
        $this->config->resolvers = array_column($resolvers, 'class');
    }
}
