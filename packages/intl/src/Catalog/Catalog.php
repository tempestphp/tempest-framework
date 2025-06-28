<?php

namespace Tempest\Intl\Catalog;

use Tempest\Intl\Locale;

interface Catalog
{
    /**
     * Determines if a translation exists for a given key in the specified locale.
     */
    public function has(Locale $locale, string $key): bool;

    /**
     * Gets the translation for a given key in the specified locale.
     */
    public function get(Locale $locale, string $key): ?string;

    /**
     * Adds a translation message for a given key in the specified locale.
     */
    public function add(Locale $locale, string $key, string $message): self;
}
