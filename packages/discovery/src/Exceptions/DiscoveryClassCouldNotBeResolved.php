<?php

namespace Tempest\Discovery\Exceptions;

use Exception;

final class DiscoveryClassCouldNotBeResolved extends Exception implements DiscoveryException
{
    public static function forDiscoveryClass(string $discoveryClass): self
    {
        return new self("Failed to resolve discovery class [{$discoveryClass}]: it is not bound in the container and cannot be instantiated without constructor arguments.");
    }
}
