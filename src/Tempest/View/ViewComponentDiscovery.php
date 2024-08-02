<?php

declare(strict_types=1);

namespace Tempest\View;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;
use Tempest\View\Components\AnonymousViewComponent;

final readonly class ViewComponentDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private ViewConfig $viewConfig,
    ) {
    }

    public function discover(ReflectionClass|string $class): void
    {
        if (is_string($class)) {
            $this->discoverPath($class);

            return;
        }

        if (! $class->isInstantiable()) {
            return;
        }

        if (! $class->implementsInterface(ViewComponent::class)) {
            return;
        }

        $this->viewConfig->addViewComponent(
            name: forward_static_call($class->getName() . '::getName'),
            viewComponent: $class,
        );
    }

    private function discoverPath(string $path): void
    {
        if (! str_ends_with($path, '.view.php')) {
            return;
        }

        if (! is_file($path)) {
            return;
        }

        $content = ltrim(file_get_contents($path));

        if (! str_contains($content, '<x-component name=')) {
            return;
        }

        preg_match(
            pattern: '/(?<header>(.|\n)*?)<x-component name="(?<name>[\w\-]+)">(?<view>(.|\n)*?)<\/x-component>/',
            subject: $content,
            matches: $matches,
        );

        if (! $matches['name']) {
            return;
        }

        $this->viewConfig->addViewComponent(
            name: $matches['name'],
            viewComponent: new AnonymousViewComponent(
                contents: $matches['header'] . $matches['view'],
                file: $path,
            ),
        );
    }

    public function createCachePayload(): string
    {
        return serialize($this->viewConfig->viewComponents);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $handlers = unserialize($payload);

        $this->viewConfig->viewComponents = $handlers;
    }
}
