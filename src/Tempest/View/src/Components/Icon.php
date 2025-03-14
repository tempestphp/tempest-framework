<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use DateInterval;
use DateTimeImmutable;
use Exception;
use Tempest\Cache\IconCache;
use Tempest\Core\AppConfig;
use Tempest\Http\Status;
use Tempest\HttpClient\HttpClient;
use Tempest\Support\Str\ImmutableString;
use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\IconConfig;
use Tempest\View\ViewComponent;

final readonly class Icon implements ViewComponent
{
    public function __construct(
        private AppConfig $appConfig,
        private IconCache $iconCache,
        private IconConfig $iconConfig,
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
            return $this->appConfig->environment->isLocal()
                ? ('<!-- unknown-icon: ' . $name . ' -->')
                : '';
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
    private function render(string $name): ?string
    {
        try {
            $parts = explode(':', $name, 2);

            if (count($parts) !== 2) {
                return null;
            }

            [$prefix, $name] = $parts;

            return $this->iconCache->resolve(
                key: "iconify-{$prefix}-{$name}",
                cache: fn () => $this->download($prefix, $name),
                expiresAt: $this->iconConfig->cacheDuration
                    ? new DateTimeImmutable()
                        ->add(DateInterval::createFromDateString("{$this->iconConfig->cacheDuration} seconds"))
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
                search: '<svg ',
                replace: "<svg class=\"{$class}\" ",
            )
            ->toString();
    }
}
