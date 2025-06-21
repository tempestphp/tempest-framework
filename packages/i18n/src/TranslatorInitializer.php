<?php

namespace Tempest\Internationalization;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Internationalization\Catalog\Catalog;
use Tempest\Internationalization\InternationalizationConfig;
use Tempest\Internationalization\MessageFormat\Formatter\MessageFormatter;

final class TranslatorInitializer implements Initializer
{
    public function initialize(Container $container): Translator
    {
        return new GenericTranslator(
            config: $container->get(InternationalizationConfig::class),
            catalog: $container->get(Catalog::class),
            formatter: $container->get(MessageFormatter::class),
        );
    }
}
