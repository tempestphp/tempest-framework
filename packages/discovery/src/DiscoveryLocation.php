<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use Tempest\Support\Filesystem;
use Tempest\Support\Namespace\Psr4Namespace;

final class DiscoveryLocation
{
    public readonly string $namespace;
    public readonly string $path;

    public string $key {
        get => hash('xxh64', $this->path);
    }

    public function __construct(
        string $namespace,
        string $path,
    ) {
        $this->namespace = $namespace;
        $this->path = Filesystem\normalize_path(rtrim($path, '\\/'));
    }

    public static function fromNamespace(Psr4Namespace $namespace): self
    {
        return new self($namespace->namespace, $namespace->path);
    }

    public function isTempest(): bool
    {
        return str_starts_with($this->namespace, 'Tempest');
    }

    public function isVendor(): bool
    {
        return str_contains($this->path, '/vendor/') || str_contains($this->path, '\\vendor\\') || $this->isTempest();
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
