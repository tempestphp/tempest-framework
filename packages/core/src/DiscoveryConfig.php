<?php

namespace Tempest\Core;

use function Tempest\Support\Filesystem\real_path;

final class DiscoveryConfig
{
    private array $skipDiscovery = [];

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

            $realpath = real_path($path);

            if ($realpath === null) {
                continue;
            }

            $this->skipDiscovery[$realpath] = true;
        }

        return $this;
    }
}
