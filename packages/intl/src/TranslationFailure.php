<?php

namespace Tempest\Intl;

use Throwable;

final readonly class TranslationFailure
{
    public function __construct(
        public Locale $locale,
        public string $key,
        public Throwable $exception,
    ) {}
}
