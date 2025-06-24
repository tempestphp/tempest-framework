<?php

namespace Tempest\Intl\MessageFormat;

interface StandaloneMarkupFormatter
{
    /**
     * Checks if the formatter supports a specific tag.
     */
    public function supportsTag(string $tag): bool;

    /**
     * Returns the formatted standalone tag.
     */
    public function format(string $tag, array $options): string;
}
