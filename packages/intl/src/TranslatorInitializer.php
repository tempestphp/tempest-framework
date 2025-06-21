<?php

namespace Tempest\Intl;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Intl\Catalog\Catalog;
use Tempest\Intl\IntlConfig;
use Tempest\Intl\MessageFormat\Formatter\MessageFormatter;

final class TranslatorInitializer implements Initializer
{
    public function initialize(Container $container): Translator
    {
        return new GenericTranslator(
            config: $container->get(IntlConfig::class),
            catalog: $container->get(Catalog::class),
            formatter: $container->get(MessageFormatter::class),
        );
    }
}
