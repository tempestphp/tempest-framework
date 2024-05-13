<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Tempest\Console\Console;

interface ComponentRenderer
{
    /**
     * @param Console $console
     * @param InteractiveComponent $component
     * @param \Tempest\Validation\Rule[] $validation
     * @return mixed
     */
    public function render(
        Console $console,
        InteractiveComponent $component,
        array $validation = [],
    ): mixed;
}
