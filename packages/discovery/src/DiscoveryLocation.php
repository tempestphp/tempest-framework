<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use Tempest\Support\Filesystem;

final class DiscoveryLocation
{
    public readonly string $namespace;
    public readonly string $path;

    public string $key {
        get => (string) crc32($this->path);
    }

    public function __construct(
        string $namespace,
        string $path,
    ) {
        $this->namespace = $namespace;
        $this->path = Filesystem\normalize_path(rtrim($path, '\\/'));
    }

    public function isVendor(): bool
    {
        return str_contains($this->path, '/vendor/') || str_contains($this->path, '\\vendor\\') || str_starts_with($this->namespace, 'Tempest');
    }

    public function toClassName(string $path): string
    {
        // Try to create a PSR-compliant class name from the path
        return str_replace(
            [
                $this->path,
                '/',
                '\\\\',
                '.php',
            ],
            [
                $this->namespace,
                '\\',
                '\\',
                '',
            ],
            $path,
        );
    }
}
