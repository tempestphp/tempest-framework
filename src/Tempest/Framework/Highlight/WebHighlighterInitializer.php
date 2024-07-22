<?php

declare(strict_types=1);

namespace Tempest\Framework\Highlight;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Themes\CssTheme;

#[Singleton(tag: 'web')]
final readonly class WebHighlighterInitializer implements Initializer
{
    public function initialize(Container $container): Highlighter
    {
        return new Highlighter(new CssTheme());
    }
}
