<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use DateInterval;
use DateTimeImmutable;
use Exception;
use Tempest\Cache\Cache;
use Tempest\HttpClient\HttpClient;
use Tempest\Support\Str\ImmutableString;
use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;

final readonly class Icon implements ViewComponent
{
    public function __construct(
        private Cache $cache,
        private HttpClient $http,
    ) {
    }

    public static function getName(): string
    {
        return 'x-icon';
    }

    public function compile(ViewComponentElement $element): string
    {
        $name = $element->getAttribute('name');
        $class = $element->getAttribute('class');

        $svg = $this->render($name);

        if (! $svg) {
            return '';
        }

        return match ($class) {
            null => $svg,
            default => $this->injectClass($svg, $class),
        };
    }

    /**
     * Downloads the icon's SVG file from the Iconify API
     */
    private function download(string $prefix, string $name): ?string
    {
        try {
            return $this->http->get("https://api.iconify.design/{$prefix}/{$name}.svg")->body;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Renders an icon
     *
     * This method is responsible for rendering the icon. If the icon is not
     * in the cache, it will download it on the fly and cache it for future
     * use. If the icon is already in the cache, it will be served from there.
     */
    private function render(string $name): ?string
    {
        try {
            $parts = explode(':', $name, 2);

            if (count($parts) !== 2) {
                return null;
            }

            [$prefix, $name] = $parts;

            return $this->cache->resolve(
                key: "iconify-{$prefix}-{$name}",
                cache: fn () => $this->download($prefix, $name),
                expiresAt: null,
            );
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Forwards the user-provided class attribute to the SVG element
     */
    private function injectClass(string $svg, string $class): string
    {
        return new ImmutableString($svg)
            ->replace(
                search: '<svg ',
                replace: "<svg class=\"{$class}\" ",
            )
            ->toString();
    }
}
