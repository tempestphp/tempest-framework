<?php

namespace Tempest\Internationalization\Translator;

use Tempest\Core\ExceptionReporter;
use Tempest\EventBus\EventBus;
use Tempest\Internationalization\Catalog\Catalog;
use Tempest\Internationalization\InternationalizationConfig;
use Tempest\Internationalization\MessageFormat\Formatter\MessageFormatter;
use Tempest\Support\Language\Locale;

final class GenericTranslator implements Translator
{
    public function __construct(
        private readonly InternationalizationConfig $config,
        private readonly Catalog $catalog,
        private readonly MessageFormatter $formatter,
        private readonly ?EventBus $eventBus = null,
    ) {}

    public function translateForLocale(Locale $locale, string $key, mixed ...$arguments): string
    {
        $message = $this->catalog->get($locale, $key);

        if (! $message) {
            $message = $this->catalog->get($this->config->fallbackLocale, $key);
        }

        if (! $message) {
            $this->eventBus?->dispatch(new TranslationMiss(
                locale: $locale,
                key: $key,
            ));

            return $key;
        }

        try {
            return $this->formatter->format($message, ...$arguments);
        } catch (\Throwable $exception) {
            $this->eventBus?->dispatch(new TranslationFailure(
                locale: $locale,
                key: $key,
                exception: $exception,
            ));

            return $key;
        }
    }

    public function translate(string $key, mixed ...$arguments): string
    {
        return $this->translateForLocale($this->config->currentLocale, $key, ...$arguments);
    }
}
