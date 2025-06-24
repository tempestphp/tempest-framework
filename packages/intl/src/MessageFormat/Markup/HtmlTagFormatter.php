<?php

namespace Tempest\Intl\MessageFormat\Markup;

use Tempest\Intl\MessageFormat\MarkupFormatter;
use Tempest\Support\Html;

final class HtmlTagFormatter implements MarkupFormatter
{
    public function supportsTag(string $tag): bool
    {
        return Html\is_html_tag($tag) && ! Html\is_void_tag($tag);
    }

    public function formatOpenTag(string $tag, array $options): string
    {
        return sprintf('<%s%s>', $tag, Html\format_attributes($options));
    }

    public function formatCloseTag(string $tag): string
    {
        return sprintf('</%s>', $tag);
    }
}
