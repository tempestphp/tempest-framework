<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Core\Priority;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class SerializerDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly SerializerFactory $serializerFactory,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(Serializer::class)) {
            return;
        }

        $context = $class->getAttribute(Context::class)->name ?? Context::DEFAULT;
        $priority = $class->getAttribute(Priority::class)->priority ?? Priority::NORMAL;

        $this->discoveryItems->add($location, [$context, $class->getName(), $priority]);
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$context, $serializerClass, $priority]) {
            $for = $serializerClass::for();

            if ($for === false) {
                continue;
            }

            $this->serializerFactory->addSerializer(
                for: $for,
                serializerClass: $serializerClass,
                context: $context,
                priority: $priority,
            );
        }
    }
}
