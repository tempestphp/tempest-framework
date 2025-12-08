<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Core\Priority;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
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

        $context = $class->getAttribute(Context::class)->name ?? Context::DEFAULT;
        $priority = $class->getAttribute(Priority::class)->priority ?? Priority::NORMAL;

        $this->discoveryItems->add($location, [$context, $class->getName(), $priority]);
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$context, $casterClass, $priority]) {
            $for = $casterClass::for();

            if ($for === false) {
                continue;
            }

            $this->casterFactory->addCaster(
                for: $for,
                casterClass: $casterClass,
                context: $context,
                priority: $priority,
            );
        }
    }
}
