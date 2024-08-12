<?php

declare(strict_types=1);

namespace Tempest\Console;

use Closure;

interface Console
{
    public function call(string $command): ExitCode;

    public function readln(): string;

    public function read(int $bytes): string;

    public function write(string $contents): self;

    public function writeln(string $line = ''): self;

    /**
     * @param InteractiveComponent $component
     * @param \Tempest\Validation\Rule[] $validation
     */
    public function component(InteractiveComponent $component, array $validation = []): mixed;

    /**
     * @param string $question
     * @param array|null $options
     * @param mixed|null $default
     * @param bool $multiple
     * @param bool $asList
     * @param \Tempest\Validation\Rule[] $validation
     * @return string|array
     */
    public function ask(
        string $question,
        ?array $options = null,
        mixed $default = null,
        bool $multiple = false,
        bool $asList = false,
        array $validation = [],
    ): string|array;

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
