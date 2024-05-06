<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Tempest\Console\Console;
use Tempest\Console\ConsoleComponent;

interface ComponentRenderer
{
    /**
     * @param Console $console
     * @param ConsoleComponent $component
     * @param \Tempest\Validation\Rule[] $validation
     * @return mixed
     */
    public function render(
        Console $console,
        ConsoleComponent $component,
        array $validation = [],
    ): mixed;
}
