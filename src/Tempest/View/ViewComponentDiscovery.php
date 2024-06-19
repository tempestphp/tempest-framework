<?php

declare(strict_types=1);

namespace Tempest\View;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\View\Components\AnonymousViewComponent;

final readonly class ViewComponentDiscovery implements Discovery
{
    private const string CACHE_PATH = __DIR__ . '/view-component-discovery.cache.php';

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

        // TODO: check if component already exists

        $this->viewConfig->addViewComponent($class);
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

        if (! str_starts_with($content, '<x-component name=')) {
            return;
        }

        preg_match(
            pattern: '/<x-component name="(?<name>[\w\-]+)">(?<view>(.|\n)*?)<\/x-component>/',
            subject: $content,
            matches: $matches,
        );

        if (! $matches['name']) {
            return;
        }

        // TODO: check if component already exists

        $this->viewConfig->viewComponents[$matches['name']] = new AnonymousViewComponent($matches['name'], $matches['view']);
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->viewConfig->viewComponents));
    }

    public function restoreCache(Container $container): void
    {
        $handlers = unserialize(file_get_contents(self::CACHE_PATH));

        $this->viewConfig->viewComponents = $handlers;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
