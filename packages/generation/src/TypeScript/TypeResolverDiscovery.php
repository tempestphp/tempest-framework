<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

use Tempest\Core\Priority;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

use function Tempest\Support\arr;

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
        $this->config->resolvers = arr([...$this->discoveryItems])
            ->sortByCallback(fn (array $a, array $b) => $a[1] <=> $b[1])
            ->map(fn (array $item) => $item[0])
            ->toArray();
    }
}
