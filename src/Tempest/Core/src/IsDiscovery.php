<?php

namespace Tempest\Core;

/** @phpstan-require-implements \Tempest\Core\Discovery */
trait IsDiscovery
{
    private DiscoveryItems $discoveryItems;

    public function getItems(): DiscoveryItems
    {
        return $this->discoveryItems;
    }

    public function setItems(DiscoveryItems $items): Discovery
    {
        $this->discoveryItems = $items;
    }
}