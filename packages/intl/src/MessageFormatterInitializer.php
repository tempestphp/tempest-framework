<?php

namespace Tempest\Intl;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Intl\MessageFormat\Formatter\MessageFormatter;
use Tempest\Intl\PluralRules\PluralRulesMatcher;

final class MessageFormatterInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): mixed
    {
        $config = $container->get(IntlConfig::class);

        return new MessageFormatter(
            functions: $config->functions,
            pluralRules: new PluralRulesMatcher(),
        );
    }
}
