<?php

namespace Tempest\Internationalization;

use Tempest\Support\Language\Locale;

interface Translator
{
    /**
     * Translates the given key with optional arguments.
     *
     * **Example**
     * ```php
     * $translator->translate('hello', name: 'Jon Doe'); // Hello, Jon Doe!
     * ```
     */
    public function translate(string $key, mixed ...$arguments): string;

    /**
     * Translates the given key for a specific locale with optional arguments.
     *
     * **Example**
     * ```php
     * $translator->translate(Locale::FRENCH, 'hello', name: 'Jon Doe'); // Bonjour, Jon Doe!
     * ```
     */
    public function translateForLocale(Locale $locale, string $key, mixed ...$arguments): string;
}
