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
     * @param array|null $options
     * @param bool $multiple
     * @param \Tempest\Validation\Rule[] $validation
     * @return string
     */
    public function ask(string $question, ?array $options = null, bool $multiple = false, array $validation = []): string|array;

    public function confirm(string $question, bool $default = false): bool;

    public function password(string $label = 'Password', bool $confirm = false): string;

    public function progressBar(iterable $data, Closure $handler): array;

    public function info(string $line): self;

    public function error(string $line): self;

    public function success(string $line): self;

    public function when(mixed $expression, callable $callback): self;
}
