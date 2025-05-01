<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\Discovery\DiscoversPath;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;
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

        $fileName = str(pathinfo($path, PATHINFO_FILENAME))->before('.');

        $contents = str(file_get_contents($path))->ltrim();

        preg_match('/(?<header>(.|\n)*?)<x-component name="(?<name>[\w\-]+)">/', $contents->toString(), $matches);

        if (isset($matches['name'])) {
            $view = $contents
                ->replaceRegex('/^(.|\n)*?<x-component.*>/', '')
                ->replaceRegex('/<\/x-component>$/', '')
                ->toString();
        } else {
            $view = $contents->toString();
        }

        if ($fileName->startsWith('x-')) {
            $this->discoveryItems->add($location, [
                $fileName->toString(),
                new AnonymousViewComponent(
                    contents: $view,
                    file: $path,
                ),
            ]);

            return;
        }

        if (! isset($matches['name'], $matches['header'])) {
            return;
        }

        $this->discoveryItems->add($location, [
            $matches['name'],
            new AnonymousViewComponent(
                contents: $matches['header'] . $view,
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
