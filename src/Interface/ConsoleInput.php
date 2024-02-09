<?php

declare(strict_types=1);

namespace Tempest\Interface;

interface ConsoleInput
{
    public function readln(): string;

    public function ask(
        string $question,
        ?array $options = null,
        ?string $default = null,
    ): string;

    public function confirm(
        string $question,
        bool $default = false,
    ): bool;
}
