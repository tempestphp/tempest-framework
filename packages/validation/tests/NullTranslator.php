<?php

namespace Tempest\Validation\Tests;

use Tempest\Intl\Locale;
use Tempest\Intl\Translator;

final class NullTranslator implements Translator
{
    public function translate(string $key, mixed ...$arguments): string
    {
        return $key;
    }

    public function translateForLocale(Locale $locale, string $key, mixed ...$arguments): string
    {
        return $key;
    }
}
