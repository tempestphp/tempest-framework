<?php

declare(strict_types=1);

namespace App;

use Tempest\Interface\Package;

final readonly class AppPackage implements Package
{
    public function __construct(
        private string $path = __DIR__,
        private string $namespace = 'App\\',
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getDiscovery(): array
    {
        return [];
    }
}
