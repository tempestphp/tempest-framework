<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Highlight\Highlighter;

final readonly class HighlighterInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Highlighter
    {
        return new Highlighter(new TempestTerminalTheme());
    }
}
