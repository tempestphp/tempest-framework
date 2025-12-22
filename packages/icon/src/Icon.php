<?php

namespace Tempest\Icon;

use Exception;
use Tempest\EventBus\EventBus;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Status;
use Tempest\HttpClient\HttpClient;
use Tempest\Support\Str;
use Tempest\Support\Str\ImmutableString;

final class Icon
{
    public function __construct(
        private IconCache $iconCache,
        private IconConfig $iconConfig,
        private HttpClient $http,
        private ?EventBus $eventBus = null,
    ) {}

    /**
     * Renders an icon as an SVG snippet. If the icon is not cached, it will be
     * downloaded on the fly and cached it for future use. If the icon is
     * already in the cache, it will be served from there.
     *
     * This method may return `null` if an error occurred in the process.
     */
    public function render(string $icon): ?string
    {
        [$collection, $iconName] = $this->parseIconIdentifier($icon) ?? [null, null];

        if ($this->iconCache->get("icon-failure-{$collection}-{$iconName}")) {
            return null;
        }

        $svg = $this->iconCache->resolve(
            key: "icon-{$collection}-{$iconName}",
            callback: fn () => $this->fetchSvg($collection, $iconName),
            expiresAt: $this->iconConfig->expiresAfter,
        );

        if ($this->iconCache->get("icon-failure-{$collection}-{$iconName}")) {
            $this->iconCache->delete("icon-{$collection}-{$iconName}");
        }

        return $svg;
    }

    /**
     * Downloads the icon's SVG file from the Iconify API
     */
    private function fetchSvg(string $collection, string $icon): ?string
    {
        try {
            $url = new ImmutableString($this->iconConfig->iconifyApiUrl)
                ->finish('/')
                ->append("{$collection}/{$icon}.svg")
                ->toString();

            $response = $this->http->get($url);

            if ($response->status !== Status::OK) {
                throw new HttpRequestFailed(
                    status: $response->status,
                    cause: $response,
                );
            }

            $this->eventBus?->dispatch(new IconDownloaded(
                collection: $collection,
                name: $icon,
                icon: $response->body,
            ));

            return $response->body;
        } catch (Exception $exception) {
            $this->eventBus?->dispatch(new IconDownloadFailed(
                collection: $collection,
                name: $icon,
                exception: $exception,
            ));

            $this->iconCache->put(
                key: "icon-failure-{$collection}-{$icon}",
                value: true,
                expiresAt: $this->iconConfig->retryAfter,
            );

            return null;
        }
    }

    /**
     * @return array{string,string}
     */
    private function parseIconIdentifier(string $identifier): ?array
    {
        if (! Str\contains($identifier, ':')) {
            $identifier = Str\replace_first($identifier, '-', ':');
        }

        $parts = explode(':', $identifier, limit: 2);

        if (count($parts) !== 2) {
            return null;
        }

        return $parts;
    }
}
