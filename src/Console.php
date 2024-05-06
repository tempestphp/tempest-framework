<?php

declare(strict_types=1);

namespace Tempest\Console;

use Closure;

interface Console
{
    public function readln(): string;

    public function read(int $bytes): string;

    public function write(string $contents): self;

    public function writeln(string $line = ''): self;

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

    /**
     * @param string $label
     * @param Closure(string $search): array $search
     * @return mixed
     */
    public function search(string $label, Closure $search): mixed;

    public function info(string $line): self;

    public function error(string $line): self;

    public function success(string $line): self;

    public function when(mixed $expression, callable $callback): self;

    public function withLabel(string $label): self;
}
