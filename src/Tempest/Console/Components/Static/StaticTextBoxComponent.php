<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Static;

use Tempest\Console\Console;
use Tempest\Console\StaticComponent;

final readonly class StaticTextBoxComponent implements StaticComponent
{
    public function __construct(
        public string $label,
    ) {
    }

    public function render(Console $console): string
    {
        $console->write("<question>{$this->label}</question> ");

        return trim($console->readln());
    }
}
