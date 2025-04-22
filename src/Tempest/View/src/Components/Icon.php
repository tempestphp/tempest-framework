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
use function Tempest\get;

final readonly class Icon implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-icon';
    }

    public function compile(ViewComponentElement $element): string
    {
        $name = $element->getAttribute('name');
        $class = $element->getAttribute('class');

        return sprintf(
            '<?= %s::render(%s, \'%s\') ?>',
            self::class,
            // Having to replace `<?=` is a bit of a hack and should be improved
            str_replace(['<?=', '?>'], '', $name),
            $class,
        );
    }

    /**
     * Renders an icon
     *
     * This method is responsible for rendering the icon. If the icon is not
     * in the cache, it will download it on the fly and cache it for future
     * use. If the icon is already in the cache, it will be served from there.
     */
    public static function render(string $name, ?string $class): ?string
    {
        $svg = self::svg($name);
        // We can't use injection because we don't have an instance of this view component at runtime. Might be worth refactoring, though.
        $appConfig = get(AppConfig::class);

        if (! $svg) {
            return $appConfig->environment->isLocal()
                ? ('<!-- unknown-icon: ' . $name . ' -->')
                : '';
        }

        if ($class !== null) {
            $svg = self::injectClass($svg, $class);
        }

        return $svg;
    }

    private static function svg(string $name): ?string
    {
        $iconCache = get(IconCache::class);

        try {
            $parts = explode(':', $name, 2);

            if (count($parts) !== 2) {
                return null;
            }

            [$prefix, $name] = $parts;

            return $iconCache->resolve(
                key: "iconify-{$prefix}-{$name}",
                cache: fn () => self::download($prefix, $name),
                expiresAt: $iconCache->cacheDuration
                    ? new DateTimeImmutable()
                        ->add(DateInterval::createFromDateString("{$iconCache->cacheDuration} seconds"))
                    : null,
            );
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Downloads the icon's SVG file from the Iconify API
     */
    private static function download(string $prefix, string $name): ?string
    {
        $iconConfig = get(IconConfig::class);
        $http = get(HttpClient::class);

        try {
            $url = new ImmutableString($iconConfig->iconifyApiUrl)
                ->finish('/')
                ->append("{$prefix}/{$name}.svg")
                ->toString();

            $response = $http->get($url);

            if ($response->status !== Status::OK) {
                return null;
            }

            return $response->body;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Forwards the user-provided class attribute to the SVG element
     */
    private static function injectClass(string $svg, string $class): string
    {
        return new ImmutableString($svg)
            ->replace(
                search: '<svg',
                replace: "<svg class=\"{$class}\"",
            )
            ->toString();
    }
}
