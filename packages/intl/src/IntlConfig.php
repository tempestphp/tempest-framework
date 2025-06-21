<?php

namespace Tempest\Intl;

use Tempest\Intl\Locale;
use Tempest\Intl\MessageFormat\FormattingFunction;

final class IntlConfig
{
    /** @var FormattingFunction[] */
    public array $functions = [];

    /** @var array<string,string[]> */
    public array $translationMessagePaths = [];

    public function __construct(
        /**
         * Defines the locale used throughout the application.
         */
        public Locale $currentLocale,

        /**
         * Defines the fallback locale used when a translation message does not exist in the current locale.
         */
        public Locale $fallbackLocale,
    ) {}

    public function addFormattingFunction(FormattingFunction $fn): void
    {
        $this->functions[] = $fn;
    }

    public function addTranslationMessageFile(Locale $locale, string $path): void
    {
        $this->translationMessagePaths[$locale->value] ??= [];

        if (! in_array($path, $this->translationMessagePaths[$locale->value], strict: true)) {
            $this->translationMessagePaths[$locale->value][] = $path;
        }
    }
}
