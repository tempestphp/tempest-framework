<?php

namespace Tempest\Internationalization;

use Tempest\Support\Language\Locale;

final readonly class TranslationFailure
{
    public function __construct(
        public Locale $locale,
        public string $key,
        public \Throwable $exception,
    ) {}
}
