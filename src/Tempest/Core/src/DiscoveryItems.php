<?php

declare(strict_types=1);

namespace Tempest\Core;

use Serializable;
use function Tempest\Support\arr;

final class DiscoveryItems
{
    public function __construct(
        private array $items = [],
    ) {}

    public function add(DiscoveryLocation $location, mixed $value): self
    {
        $this->items[$location->path] ??= [];

        $this->items[$location->path][] = $value;

        return $this;
    }

    public function hasLocation(DiscoveryLocation $location): bool
    {
        return array_key_exists($location->path, $this->items);
    }

    // TODO: make this class directly iterable
    public function flatten(): array
    {
        return arr($this->items)->flatten(1)->toArray();
    }

    public function isLoaded(): bool
    {
        return $this->items !== [];
    }

    public function onlyVendor(): self
    {
        return new self(
            arr($this->items)
                ->filter(fn (array $items, string $path) => str_contains($path, '/vendor/') || str_contains($path, '\\vendor\\'))
                ->toArray(),
        );
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function __serialize(): array
    {
        return $this->items;
    }

    public function __unserialize(array $data): void
    {
        $this->items = $data;
    }
}
