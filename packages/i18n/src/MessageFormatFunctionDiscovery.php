<?php

declare(strict_types=1);

namespace Tempest\Internationalization;

use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Internationalization\MessageFormat\Formatter\MessageFormatFunction;
use Tempest\Reflection\ClassReflector;

final class MessageFormatFunctionDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly Container $container,
        private readonly InternationalizationConfig $config,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(MessageFormatFunction::class)) {
            return;
        }

        $this->discoveryItems->add($location, $class->getName());
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $className) {
            $this->config->addMessageFormatFunction($this->container->get($className));
        }
    }
}
