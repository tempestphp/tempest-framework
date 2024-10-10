<?php

declare(strict_types=1);

namespace Tempest\View;

/** @phpstan-require-implements \Tempest\View\View */
trait IsView
{
    public string $path;

    public array $data = [];

    public function __construct(
        string $path,
        array $data = [],
    ) {
        $this->path = $path;
        $this->data = $data;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getData(): array
    {
        return $this->data;
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
