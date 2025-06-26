<?php

namespace Tempest\Intl\MessageFormat\Markup;

use Tempest\Container\Container;
use Tempest\Intl\MessageFormat\StandaloneMarkupFormatter;
use Tempest\Support\Arr;
use Tempest\Support\Str;
use Tempest\View\Components\Icon;

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
            throw new \RuntimeException('The `tempest\view` package is required to use the `icon` tag inside a translation string.');
        }

        return $this->container->get(Icon::class)->render(
            name: Str\after_first($tag, 'icon-'),
            class: Arr\get_by_key($options, 'class'),
        );
    }
}
