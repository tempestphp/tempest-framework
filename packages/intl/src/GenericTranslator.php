<?php

namespace Tempest\Intl;

use Tempest\EventBus\EventBus;
use Tempest\Intl\Catalog\Catalog;
use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;
use Tempest\Intl\MessageFormat\Formatter\MessageFormatter;

final readonly class GenericTranslator implements Translator
{
    public function __construct(
        private IntlConfig $config,
        private Catalog $catalog,
        private MessageFormatter $formatter,
        private ?EventBus $eventBus = null,
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
