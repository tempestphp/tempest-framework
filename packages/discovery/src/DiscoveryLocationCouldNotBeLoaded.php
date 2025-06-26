<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use Exception;

final class DiscoveryLocationCouldNotBeLoaded extends Exception
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('Could not locate %s, try running "composer install"', $path));
    }
}
