<?php

namespace Tempest\Intl;

use Tempest\Intl\Locale;
use Throwable;

final readonly class TranslationFailure
{
    public function __construct(
        public Locale $locale,
        public string $key,
        public Throwable $exception,
    ) {}
}
