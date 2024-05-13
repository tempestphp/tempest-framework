<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Renderers;

use Exception;
use Tempest\Console\Components\ComponentRenderer;
use Tempest\Console\Components\InteractiveComponent;
use Tempest\Console\Console;

final class UnsupportedComponentRenderer implements ComponentRenderer
{
    public function render(
        Console $console,
        InteractiveComponent $component,
        array $validation = []
    ): mixed {
        throw new Exception('Unsupported');
    }
}
