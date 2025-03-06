<?php

declare(strict_types=1);

namespace Tempest\View;

use function Tempest\Support\path;

/** @phpstan-require-implements \Tempest\View\View */
trait IsView
{
    public string $path;

    public ?string $relativeRootPath = null;

    public array $data = [];

    public function __construct(
        string $path,
        array $data = [],
    ) {
        $this->path = $path;
        $this->data = $data;

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        if (str_ends_with($trace[0]['file'], 'Tempest/View/src/functions.php')) {
            $this->relativeRootPath = path($trace[1]['file'])->dirname();
        } else {
            $this->relativeRootPath = path($trace[0]['file'])->dirname();
        }
    }

    public function get(string $key): mixed
    {
        return $this->{$key} ?? $this->data[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data) || property_exists($this, $key);
    }

    public function data(mixed ...$params): self
    {
        $this->data = [...$this->data, ...$params];

        return $this;
    }
}
