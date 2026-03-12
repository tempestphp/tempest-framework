<?php

namespace Tests\Tempest\Integration\Icon;

use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Clock\Clock;
use Tempest\DateTime\Duration;
use Tempest\EventBus\EventBus;
use Tempest\Http\GenericResponse;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Status;
use Tempest\HttpClient\HttpClient;
use Tempest\Icon;
use Tempest\Icon\IconConfig;
use Tempest\Icon\IconDownloaded;
use Tempest\Icon\IconDownloadFailed;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class IconTest extends FrameworkIntegrationTestCase
{
    public function test_icon_render(): void
    {
        $this->registerMocks();

        $icon = $this->container->get(Icon\Icon::class);

        $this->assertSame('<svg></svg>', $icon->render('mdi:tsunami'));
    }

    public function test_icon_function(): void
    {
        $this->registerMocks();

        $this->assertSame('<svg></svg>', Icon\render('mdi:tsunami'));
    }

    public function test_cache_expiry(): void
    {
        $this->registerMocks(
            clock: $clock = $this->clock(),
            serverHits: $this->exactly(2),
        );

        $icon = $this->container->get(Icon\Icon::class);

        // first fetch, we hit server
        $this->assertSame('<svg></svg>', $icon->render('mdi:tsunami'));

        // second fetch, we hit cache
        $this->assertSame('<svg></svg>', $icon->render('mdi:tsunami'));

        // fetch after expiry, we hit server
        $clock->plus(Duration::hour());
        $this->assertSame('<svg></svg>', $icon->render('mdi:tsunami'));

        // another fetch, cached again
        $this->assertSame('<svg></svg>', $icon->render('mdi:tsunami'));
    }

    public function test_failure_retry_after(): void
    {
        $this->registerMocks(
            response: new NotFound(),
            clock: $clock = $this->clock(),
            serverHits: $this->exactly(2),
        );

        $iconCache = $this->container->get(Icon\IconCache::class);
        $icon = $this->container->get(Icon\Icon::class);

        // first fetch, we cache the failure
        $this->assertNull($icon->render('mdi:tsunami'));
        $this->assertTrue($iconCache->get('icon-failure-mdi-tsunami'));

        // the failure expires
        $clock->plus(Duration::minute());
        $this->assertNull($iconCache->get('icon-failure-mdi-tsunami'));

        // second fetch, ensures the same thing happened
        $this->assertNull($icon->render('mdi:tsunami'));
        $this->assertTrue($iconCache->get('icon-failure-mdi-tsunami'));
    }

    public function test_downloaded_event(): void
    {
        $this->registerMocks(response: new NotFound());

        $hasFailed = false;
        $icon = $this->container->get(Icon\Icon::class);
        $eventBus = $this->container->get(EventBus::class);

        $eventBus->listen(function (IconDownloadFailed $event) use (&$hasFailed): void {
            $hasFailed = true;
            $this->assertSame('mdi', $event->collection);
            $this->assertSame('tsunami', $event->name);
            $this->assertInstanceOf(HttpRequestFailed::class, $event->exception);
        });

        $this->assertNull($icon->render('mdi:tsunami'));
        $this->assertTrue($hasFailed);
    }

    public function test_download_failed_event(): void
    {
        $this->registerMocks();

        $wasDownloaded = false;
        $icon = $this->container->get(Icon\Icon::class);
        $eventBus = $this->container->get(EventBus::class);

        $eventBus->listen(function (IconDownloaded $event) use (&$wasDownloaded): void {
            $wasDownloaded = true;
            $this->assertSame('mdi', $event->collection);
            $this->assertSame('tsunami', $event->name);
            $this->assertSame('<svg></svg>', $event->icon);
        });

        $this->assertSame('<svg></svg>', $icon->render('mdi:tsunami'));
        $this->assertTrue($wasDownloaded);
    }

    private function registerMocks(
        ?Response $response = null,
        ?Clock $clock = null,
        ?InvokedCount $serverHits = null,
    ): void {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($serverHits ?? $this->atLeastOnce())
            ->method('get')
            ->with('https://api.iconify.test/mdi/tsunami.svg')
            ->willReturn($response ?? new GenericResponse(status: Status::OK, body: '<svg></svg>'));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $this->container->singleton(Icon\IconCache::class, new Icon\IconCache(
            enabled: true,
            pool: new ArrayAdapter(clock: $clock?->toPsrClock()),
        ));

        $this->container->singleton(IconConfig::class, new IconConfig(
            iconifyApiUrl: 'https://api.iconify.test',
            retryAfter: Duration::minute(),
            expiresAfter: Duration::hour(),
        ));
    }
}
