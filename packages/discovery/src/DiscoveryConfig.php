<?php

namespace Tempest\Discovery;

use Tempest\Support\Filesystem;

final class DiscoveryConfig
{
    private array $skipDiscovery = [];

    /** @var array<array-key, class-string<\Tempest\Discovery\Discovery>> The loaded discovery classes that will be used during discovery */
    public array $classes = [];

    public function __construct(
        /** @var \Tempest\Discovery\DiscoveryLocation[] Locations that should be scanned during discovery */
        public array $locations = [],
    ) {}

    public static function autoload(string $path): self
    {
        return new self(
            locations: (new AutoloadDiscoveryLocations($path))(),
        );
    }

    public function shouldSkip(string $input): bool
    {
        return $this->skipDiscovery[$input] ?? false;
    }

    public function skipClasses(string ...$classNames): self
    {
        foreach ($classNames as $className) {
            $this->skipDiscovery[$className] = true;
        }

        return $this;
    }

    public function skipPaths(string ...$paths): self
    {
        foreach ($paths as $path) {
            $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);

            $realpath = Filesystem\normalize_path($path);

            if ($realpath === null) {
                continue;
            }

            $this->skipDiscovery[$realpath] = true;
        }

        return $this;
    }
}
