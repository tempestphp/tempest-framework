<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Static;

use Tempest\Console\Console;
use Tempest\Console\StaticComponent;

final readonly class StaticConfirmComponent implements StaticComponent
{
    public function __construct(
        private string $question,
        private bool $default = false,
    ) {
    }

    public function render(Console $console): bool
    {
        $answer = $console->ask(
            question: $this->question,
            options: ['yes', 'no'],
            default: $this->default ? 'yes' : 'no',
        );

        return $answer === 'yes';
    }
}
