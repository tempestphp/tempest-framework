<?php

declare(strict_types=1);

namespace Tempest\View;

trait IsView
{
    public string $path;
    public array $data = [];
    private array $rawData = [];

    public function __construct(
        string $path,
        array $data = [],
    )
    {
        $this->path = $path;
        $this->data = $this->escape($data);
        $this->rawData = $data;
    }

    public function __get(string $name)
    {
        $value = $this->data[$name] ?? null;

        if (is_string($value)) {
            $value = htmlentities($value);
        }

        return $value;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }

    public function getRaw(string $key): mixed
    {
        return $this->rawData[$key] ?? null;
    }

    public function get(string $key): mixed
    {
        return $this->{$key} ?? $this->data[$key] ?? null;
    }

    public function data(...$params): self
    {
        $this->rawData = [...$this->rawData, ...$params];
        $this->data = [...$this->data, ...$this->escape($params)];

        return $this;
    }

    public function raw(string $name): ?string
    {
        return $this->rawData[$name] ?? null;
    }

    private function escape(array $items): array
    {
        foreach ($items as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            $items[$key] = htmlentities($value);
        }

        return $items;
    }

    public function eval(string $eval): mixed
    {
        /** @phpstan-ignore-next-line */
        return eval("return {$eval};");
    }
}
