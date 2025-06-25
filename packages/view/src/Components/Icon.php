<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Exception;
use Tempest\Clock\Clock;
use Tempest\Core\AppConfig;
use Tempest\Http\Status;
use Tempest\HttpClient\HttpClient;
use Tempest\Support\Html\HtmlString;
use Tempest\Support\Str;
use Tempest\Support\Str\ImmutableString;
use Tempest\View\IconCache;
use Tempest\View\IconConfig;

final readonly class Icon
{
    public function __construct(
        private AppConfig $appConfig,
        private IconCache $iconCache,
        private IconConfig $iconConfig,
        private HttpClient $http,
        private Clock $clock,
    ) {}

    public function render(string $name, ?string $class = null): HtmlString
    {
        $html = $this->svg($name);

        if (! $html) {
            return new HtmlString($this->appConfig->environment->isLocal()
                ? ('<!-- unknown-icon: ' . $name . ' -->')
                : '');
        }

        if ($class) {
            $html = $this->injectClass($html, $class);
        }

        return new HtmlString($html);
    }

    /**
     * Downloads the icon's SVG file from the Iconify API
     */
    private function download(string $prefix, string $name): ?string
    {
        try {
            $url = new ImmutableString($this->iconConfig->iconifyApiUrl)
                ->finish('/')
                ->append("{$prefix}/{$name}.svg")
                ->toString();

            $response = $this->http->get($url);

            if ($response->status !== Status::OK) {
                return null;
            }

            return $response->body;
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
    private function svg(string $name): ?string
    {
        try {
            if (! Str\contains($name, ':')) {
                $name = Str\replace_first($name, '-', ':');
            }

            $parts = explode(':', $name, limit: 2);

            if (count($parts) !== 2) {
                return null;
            }

            [$prefix, $name] = $parts;

            return $this->iconCache->resolve(
                key: "iconify-{$prefix}-{$name}",
                callback: fn () => $this->download($prefix, $name),
                expiresAt: $this->iconConfig->cacheDuration
                    ? $this->clock->now()->plusSeconds($this->iconConfig->cacheDuration)
                    : null,
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
                search: '<svg',
                replace: "<svg class=\"{$class}\"",
            )
            ->toString();
    }
}
