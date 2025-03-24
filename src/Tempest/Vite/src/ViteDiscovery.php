<?php

declare(strict_types=1);

namespace Tempest\Vite;

use Tempest\Discovery\DiscoversPath;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;
use Tempest\Vite\ViteConfig;

use function Tempest\Support\str;
use function Tempest\Support\Str\ends_with;

final class ViteDiscovery implements Discovery, DiscoversPath
{
    use IsDiscovery;

    public function __construct(
        private readonly ViteConfig $viteConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        return;
    }

    public function discoverPath(DiscoveryLocation $location, string $path): void
    {
        if (! ends_with($path, ['.ts', '.css', '.js'])) {
            return;
        }

        if (! str($path)->beforeLast('.')->endsWith('.entrypoint')) {
            return;
        }

        if (! is_file($path)) {
            return;
        }

        $this->discoveryItems->add($location, [$path]);
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$path]) {
            $this->viteConfig->addEntrypoint($path);
        }
    }
}
