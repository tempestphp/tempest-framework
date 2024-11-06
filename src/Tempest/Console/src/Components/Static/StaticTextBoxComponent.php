<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Static;

use Tempest\Console\Console;
use Tempest\Console\StaticConsoleComponent;

final readonly class StaticTextBoxComponent implements StaticConsoleComponent
{
    public function __construct(
        public string $label,
        public ?string $default = null,
    ) {
    }

    public function render(Console $console): ?string
    {
        if (! $console->supportsPrompting()) {
            return $this->default;
        }

        $console->write("<question>{$this->label}</question> ");

        return trim($console->readln()) ?: $this->default;
    }
}
