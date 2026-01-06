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
            /** @var Shell */
            return $this->console->ask(
                question: $question,
                options: Shell::class,
                default: $detected,
            );
        }

        return $detected;
    }
}
