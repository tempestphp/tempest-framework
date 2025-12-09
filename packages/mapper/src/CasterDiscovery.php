<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Core\Priority;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Mapper\Attributes\Context;
use Tempest\Reflection\ClassReflector;

final class CasterDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly CasterFactory $casterFactory,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(Caster::class)) {
            return;
        }

        $this->discoveryItems->add($location, [
            $class->getAttribute(Context::class)?->name,
            $class->getAttribute(Priority::class)?->priority,
            $class->getName(),
        ]);
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$context, $priority, $casterClass]) {
            $for = $casterClass::for();

            if ($for === false) {
                continue;
            }

            $this->casterFactory->addCaster(
                for: $for,
                casterClass: $casterClass,
                priority: $priority ?? Priority::NORMAL,
                context: $context,
            );
        }
    }
}
