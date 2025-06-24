<?php

namespace Tempest\Intl;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Intl\MessageFormat\Formatter\MessageFormatter;

final class MessageFormatterInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): MessageFormatter
    {
        $config = $container->get(IntlConfig::class);

        return new MessageFormatter(
            functions: $config->functions,
            markupFormatters: $config->markupFormatters,
        );
    }
}
