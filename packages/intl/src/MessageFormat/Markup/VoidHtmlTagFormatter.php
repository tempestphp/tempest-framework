<?php

namespace Tempest\Intl\MessageFormat\Markup;

use Tempest\Intl\MessageFormat\StandaloneMarkupFormatter;
use Tempest\Support\Html;

final class VoidHtmlTagFormatter implements StandaloneMarkupFormatter
{
    public function supportsTag(string $tag): bool
    {
        return Html\is_void_tag($tag);
    }

    public function format(string $tag, array $options): string
    {
        return Html\create_tag($tag, $options)->toString();
    }
}
