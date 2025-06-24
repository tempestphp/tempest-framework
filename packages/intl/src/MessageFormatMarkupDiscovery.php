<?php

declare(strict_types=1);

namespace Tempest\Intl;

use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Intl\MessageFormat\MarkupFormatter;
use Tempest\Intl\MessageFormat\StandaloneMarkupFormatter;
use Tempest\Reflection\ClassReflector;

final class MessageFormatMarkupDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly Container $container,
        private readonly IntlConfig $config,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(MarkupFormatter::class) && ! $class->implements(StandaloneMarkupFormatter::class)) {
            return;
        }

        $this->discoveryItems->add($location, $class->getName());
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $className) {
            $this->config->addMarkupFormatter($this->container->get($className));
        }
    }
}
