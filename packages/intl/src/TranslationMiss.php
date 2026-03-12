<?php

namespace Tempest\Intl;

use Tempest\Intl\Locale;

final readonly class TranslationMiss
{
    public function __construct(
        public Locale $locale,
        public string $key,
    ) {}
}
