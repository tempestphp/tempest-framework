<?php

declare(strict_types=1);

namespace Tempest\Intl;

use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Intl\MessageFormat\FormattingFunction;
use Tempest\Intl\MessageFormat\SelectorFunction;
use Tempest\Reflection\ClassReflector;

final class MessageFormatFunctionDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly Container $container,
        private readonly IntlConfig $config,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(FormattingFunction::class) && ! $class->implements(SelectorFunction::class)) {
            return;
        }

        $this->discoveryItems->add($location, $class->getName());
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $className) {
            $this->config->addFunction($this->container->get($className));
        }
    }
}
