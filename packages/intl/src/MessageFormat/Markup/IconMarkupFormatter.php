<?php

namespace Tempest\Intl\MessageFormat\Markup;

use Tempest\Container\Container;
use Tempest\Icon\Icon;
use Tempest\Intl\MessageFormat\StandaloneMarkupFormatter;
use Tempest\Support\Str;

final readonly class IconMarkupFormatter implements StandaloneMarkupFormatter
{
    public function __construct(
        private Container $container,
    ) {}

    public function supportsTag(string $tag): bool
    {
        return Str\starts_with($tag, 'icon-');
    }

    public function format(string $tag, array $options): string
    {
        if (! class_exists(Icon::class)) {
            throw new \RuntimeException('The `tempest\icon` package is required to use the `icon` tag inside a translation string.');
        }

        $icon = Str\after_first($tag, 'icon-');

        return $this->container->get(Icon::class)->render($icon);
    }
}
