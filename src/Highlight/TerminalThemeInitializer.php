<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Highlight\TerminalTheme;

#[Singleton]
final readonly class TerminalThemeInitializer implements Initializer
{
    public function initialize(Container $container): TerminalTheme
    {
        return new TempestTerminalTheme();
    }
}
