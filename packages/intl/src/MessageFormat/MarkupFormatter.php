<?php

namespace Tempest\Intl\MessageFormat;

interface MarkupFormatter
{
    /**
     * Checks if the formatter supports a specific tag.
     */
    public function supportsTag(string $tag): bool;

    /**
     * Returns the formatted markup for an opening tag with options.
     */
    public function formatOpenTag(string $tag, array $options): string;

    /**
     * Returns the formatted markup for a closing tag.
     */
    public function formatCloseTag(string $tag): string;
}
