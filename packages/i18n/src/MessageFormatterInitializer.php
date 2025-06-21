<?php

namespace Tempest\Internationalization;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Internationalization\MessageFormat\Formatter\MessageFormatter;
use Tempest\Internationalization\PluralRules\PluralRulesMatcher;

final class MessageFormatterInitializer implements Initializer
{
    public function initialize(Container $container): mixed
    {
        $config = $container->get(InternationalizationConfig::class);

        return new MessageFormatter(
            functions: $config->functions,
            pluralRules: new PluralRulesMatcher(),
        );
    }
}
