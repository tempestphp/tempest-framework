<?php

namespace Tempest\Intl;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\EventBus\EventBus;
use Tempest\Intl\Catalog\Catalog;
use Tempest\Intl\IntlConfig;
use Tempest\Intl\MessageFormat\Formatter\MessageFormatter;

final class TranslatorInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Translator
    {
        return new GenericTranslator(
            config: $container->get(IntlConfig::class),
            catalog: $container->get(Catalog::class),
            formatter: $container->get(MessageFormatter::class),
            eventBus: $container->get(EventBus::class),
        );
    }
}
