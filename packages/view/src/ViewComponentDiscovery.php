<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\Discovery\DiscoversPath;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Filesystem;
use function Tempest\Support\str;

final class ViewComponentDiscovery implements Discovery, DiscoversPath
{
    use IsDiscovery;

    public function __construct(
        private readonly ViewConfig $viewConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        // Only view paths are discovered
    }

    public function discoverPath(DiscoveryLocation $location, string $path): void
    {
        $baseName = str(pathinfo($path, PATHINFO_BASENAME));

        if (! $baseName->endsWith('.view.php')) {
            return;
        }

        if (! $baseName->startsWith('x-')) {
            return;
        }

        if (! Filesystem\is_file($path)) {
            return;
        }

        $contents = str(Filesystem\read_file($path))->ltrim();

        $this->discoveryItems->add($location, [
            $path,
            new ViewComponent(
                name: $baseName->before('.view.php')->toString(),
                contents: $contents->toString(),
                file: $path,
                isVendorComponent: $location->isVendor(),
            ),
        ]);
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$name, $viewComponent]) {
            $this->viewConfig->addViewComponent(
                pending: $viewComponent,
            );
        }
    }
}
