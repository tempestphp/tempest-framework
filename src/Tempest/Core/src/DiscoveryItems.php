<?php

declare(strict_types=1);

namespace Tempest\Core;

use function Tempest\Support\arr;

final class DiscoveryItems
{
    private array $items = [];

    public function add(DiscoveryLocation $location, mixed $value): self
    {
        $this->items[$location->path] ??= [];

        $this->items[$location->path][] = $value;

        return $this;
    }

    public function without(DiscoveryLocation ...$locations): array
    {
        return arr($this->items)
            ->filter(fn (mixed $value, string $location) => ! in_array($location, $locations))
            ->toArray();
    }

    // TODO: make this class directly iterable
    public function flatten(): array
    {
        return arr($this->items)->flatten(1)->toArray();
    }
}
