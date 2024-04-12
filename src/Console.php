<?php

declare(strict_types=1);

namespace Tempest\Console;

interface Console extends ConsoleInput, ConsoleOutput
{
    public function component(ConsoleComponent $component): mixed;

    public function ask(string $question, ?array $options = null): string;

    public function confirm(string $question, bool $default = false): bool;
}
