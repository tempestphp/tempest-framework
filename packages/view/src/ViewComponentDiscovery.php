<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\Discovery\DiscoversPath;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Str\ImmutableString;
use Tempest\View\Components\AnonymousViewComponent;

use function Tempest\Support\str;

final class ViewComponentDiscovery implements Discovery, DiscoversPath
{
    use IsDiscovery;

    public function __construct(
        private readonly ViewConfig $viewConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(ViewComponent::class)) {
            return;
        }

        $this->discoveryItems->add($location, [
            forward_static_call($class->getName() . '::getName'),
            $class->getName(),
        ]);
    }

    public function discoverPath(DiscoveryLocation $location, string $path): void
    {
        if (! str_ends_with($path, '.view.php')) {
            return;
        }

        if (! is_file($path)) {
            return;
        }

        $contents = str(file_get_contents($path))->ltrim();

        $fileName = str(pathinfo($path, PATHINFO_FILENAME))->before('.');

        if ($fileName->startsWith('x-')) {
            $this->registerFileComponent(
                location: $location,
                path: $path,
                fileName: $fileName,
                contents: $contents,
            );
        } elseif ($contents->contains('<x-component name="')) {
            $this->registerElementComponent(
                location: $location,
                path: $path,
                contents: $contents,
            );
        }
    }

    private function registerElementComponent(DiscoveryLocation $location, string $path, ImmutableString $contents): void
    {
        $header = $contents
            ->before('<x-component name="')
            ->toString();

        $name = $contents
            ->afterFirst('<x-component name="')
            ->before('"')
            ->toString();

        $view = $contents
            ->afterFirst('<x-component name="' . $name . '">')
            ->beforeLast('</x-component>')
            ->toString();

        $this->discoveryItems->add($location, [
            $name,
            new AnonymousViewComponent(
                contents: $header . $view,
                file: $path,
            ),
        ]);
    }

    private function registerFileComponent(
        DiscoveryLocation $location,
        string $path,
        ImmutableString $fileName,
        ImmutableString $contents
    ): void {
        $this->discoveryItems->add($location, [
            $fileName->toString(),
            new AnonymousViewComponent(
                contents: $contents->toString(),
                file: $path,
            ),
        ]);
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$name, $viewComponent]) {
            if (is_string($viewComponent)) {
                $viewComponent = new ClassReflector($viewComponent);
            }

            $this->viewConfig->addViewComponent(
                name: $name,
                viewComponent: $viewComponent,
            );
        }
    }
}
