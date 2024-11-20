<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Reflection\ClassReflector;

interface Discovery
{
    public function discover(DiscoveryLocation $location, ClassReflector $class): void;

    public function getItems(): DiscoveryItems;

    public function setItems(DiscoveryItems $items): Discovery;

    public function apply(): void;
}
