<?php

declare(strict_types=1);

namespace Tempest\Console;

use Closure;

interface Console extends ConsoleInput, ConsoleOutput
{
    /**
     * @param ConsoleComponent $component
     * @param \Tempest\Validation\Rule[] $validation
     */
    public function component(ConsoleComponent $component, array $validation = []): mixed;

    /**
     * @param string $question
     * @param array $options
     * @param \Tempest\Validation\Rule[] $validation
     */
    public function ask(string $question, ?array $options = null, array $validation = []): string;

    public function confirm(string $question, bool $default = false): bool;

    public function password(string $label = 'Password', bool $confirm = false): string;

    public function progressBar(iterable $data, Closure $handler): array;
}
