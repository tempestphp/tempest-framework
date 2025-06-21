<?php

namespace Tempest\Internationalization\Translator;

use Tempest\Support\Language\Locale;

final readonly class TranslationMiss
{
    public function __construct(
        public Locale $locale,
        public string $key,
    ) {}
}
