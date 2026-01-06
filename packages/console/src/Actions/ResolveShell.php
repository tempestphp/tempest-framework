<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\Console\Console;
use Tempest\Console\Enums\Shell;

final readonly class ResolveShell
{
    public function __construct(
        private Console $console,
    ) {}

    public function __invoke(string $question = 'Which shell?'): ?Shell
    {
        $detected = Shell::detect();

        if ($this->console->supportsPrompting()) {
            $options = [];

            foreach (Shell::cases() as $shellCase) {
                $label = $shellCase->value;

                if ($shellCase === $detected) {
                    $label .= ' (current)';
                }

                $options[$shellCase->value] = $label;
            }

            $choice = $this->console->ask(
                question: $question,
                options: $options,
                default: $detected?->value,
            );

            return Shell::from($choice);
        }

        return $detected;
    }
}
