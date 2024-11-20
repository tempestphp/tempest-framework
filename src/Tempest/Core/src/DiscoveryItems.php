<?php

namespace Tempest\Core;

use Tempest\Support\ArrayHelper;

final class DiscoveryItems
{
    private ArrayHelper $items;

    public function __construct()
    {
        $this->items = new ArrayHelper();
    }

    public function add(DiscoveryLocation $location, mixed $value): self
    {
        $this->items[$location->path][] = $value;

        return $this;
    }

    public function without(DiscoveryLocation ...$locations): ArrayHelper
    {
        return $this
            ->items
            ->filter(fn (mixed $value, string $location) => ! in_array($location, $locations));
    }

    // TODO: make this class directly iterable
    public function flatten(): ArrayHelper
    {
        return $this->items->flatten();
    }
}