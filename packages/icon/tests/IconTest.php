<?php

namespace Tempest\Icon\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Container\GenericContainer;
use Tempest\DateTime\Duration;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\EventBusConfig;
use Tempest\EventBus\GenericEventBus;
use Tempest\Http\GenericResponse;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Status;
use Tempest\HttpClient\HttpClient;
use Tempest\Icon\Icon;
use Tempest\Icon\IconCache;
use Tempest\Icon\IconConfig;
use Tempest\Icon\IconDownloaded;
use Tempest\Icon\IconDownloadFailed;

final class IconTest extends TestCase
{
    public function test_rendering(): void
    {
        $icon = new Icon(
            iconCache: new IconCache(pool: new ArrayAdapter()),
            iconConfig: $this->createIconConfig(),
            http: $this->mockHttpClient(),
            eventBus: null,
        );

        $this->assertSame('<svg></svg>', $icon->render('ph:eye'));
    }

    public function test_is_cached_after_first_render(): void
    {
        $icon = new Icon(
            iconCache: $cache = new IconCache(pool: new ArrayAdapter()),
            iconConfig: $this->createIconConfig(),
            http: $this->mockHttpClient(),
            eventBus: null,
        );

        $this->assertSame('<svg></svg>', $icon->render('ph:eye'));
        $this->assertSame('<svg></svg>', $cache->get('icon-ph-eye'));
        $this->assertSame('<svg></svg>', $icon->render('ph:eye'));
    }

    public function test_event_downloaded(): void
    {
        if (! interface_exists(EventBus::class)) {
            $this->markTestSkipped('EventBus is not available.');
        }

        $icon = new Icon(
            iconCache: new IconCache(pool: new ArrayAdapter()),
            iconConfig: $this->createIconConfig(),
            http: $this->mockHttpClient(),
            eventBus: $eventBus = $this->createEventBus(),
        );

        $wasDownloaded = false;
        $eventBus->listen(function (IconDownloaded $event) use (&$wasDownloaded) {
            $wasDownloaded = true;
            $this->assertSame('ph', $event->collection);
            $this->assertSame('eye', $event->name);
            $this->assertSame('<svg></svg>', $event->icon);
        });

        $this->assertSame('<svg></svg>', $icon->render('ph:eye'));
        $this->assertTrue($wasDownloaded);
    }

    public function test_event_failed(): void
    {
        if (! interface_exists(EventBus::class)) {
            $this->markTestSkipped('EventBus is not available.');
        }

        $icon = new Icon(
            iconCache: new IconCache(pool: new ArrayAdapter()),
            iconConfig: $this->createIconConfig(),
            http: $this->mockHttpClient(new NotFound()),
            eventBus: $eventBus = $this->createEventBus(),
        );

        $hasFailed = false;
        $eventBus->listen(function (IconDownloadFailed $event) use (&$hasFailed) {
            $hasFailed = true;
            $this->assertSame('ph', $event->collection);
            $this->assertSame('eye', $event->name);
            $this->assertInstanceOf(HttpRequestFailed::class, $event->exception);
        });

        $this->assertNull($icon->render('ph:eye'));
        $this->assertTrue($hasFailed);
    }

    private function createIconConfig(): IconConfig
    {
        return new IconConfig(
            iconifyApiUrl: 'https://api.iconify.design',
            retryAfter: Duration::hours(12),
            expiresAfter: null,
        );
    }

    private function createEventBus(): EventBus
    {
        return new GenericEventBus(
            container: new GenericContainer(),
            eventBusConfig: new EventBusConfig(),
        );
    }

    private function mockHttpClient(?Response $response = null): mixed
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.design/ph/eye.svg')
            ->willReturn($response ?? new GenericResponse(status: Status::OK, body: '<svg></svg>'));

        return $mockHttpClient;
    }
}
