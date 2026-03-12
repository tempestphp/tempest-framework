<?php

namespace Tempest\Intl;

final readonly class TranslationMiss
{
    public function __construct(
        public Locale $locale,
        public string $key,
    ) {}
}
