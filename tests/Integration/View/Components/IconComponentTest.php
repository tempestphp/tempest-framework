<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View\Components;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\ConfigCache;
use Tempest\Core\Environment;
use Tempest\DateTime\Duration;
use Tempest\Http\GenericResponse;
use Tempest\Http\Status;
use Tempest\HttpClient\HttpClient;
use Tempest\Icon\IconCache;
use Tempest\Icon\IconConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\View\view;

final class IconComponentTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->get(ConfigCache::class)->clear();

        $iconCache = $this->container->get(IconCache::class);
        $iconCache->enabled = true;
        $iconCache->clear();
    }

    #[Test]
    public function it_renders_an_icon(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->once())
            ->method('get')
            ->with('https://api.iconify.design/material-symbols/php.svg')
            ->willReturn(new GenericResponse(
                status: Status::OK,
                body: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            ));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $this->assertSame(
            '<svg width="1em" height="1em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            $this->view->render('<x-icon name="material-symbols:php" />'),
        );
    }

    #[Test]
    public function it_downloads_the_icon_from_a_custom_api(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.test/material-symbols/php.svg')
            ->willReturn(new GenericResponse(
                status: Status::OK,
                body: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            ));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $this->container->singleton(
            IconConfig::class,
            fn () => new IconConfig(iconifyApiUrl: 'https://api.iconify.test', retryAfter: Duration::hours(12)),
        );

        $this->assertSame(
            '<svg width="1em" height="1em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            $this->view->render('<x-icon name="material-symbols:php" />'),
        );
    }

    #[Test]
    public function fallback_without_name(): void
    {
        $this->assertSame(
            '',
            $this->view->render('<x-icon />'),
        );
    }

    #[Test]
    public function it_caches_icons_on_the_first_render(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->once())
            ->method('get')
            ->with('https://api.iconify.design/material-symbols/php.svg')
            ->willReturn(new GenericResponse(
                status: Status::OK,
                body: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            ));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $this->view->render('<x-icon name="material-symbols:php" />');

        $iconCache = $this->container->get(IconCache::class);
        $cachedIcon = $iconCache->get('icon-material-symbols-php');

        $this->assertNotNull($cachedIcon);
        $this->assertSame(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            $cachedIcon,
        );
    }

    #[Test]
    public function it_renders_an_icon_from_cache(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.design/material-symbols/php.svg')
            ->willReturn(new GenericResponse(
                status: Status::OK,
                body: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            ));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        // Trigger first render, which should cache the icon
        $this->view->render('<x-icon name="material-symbols:php" />');

        $this->assertSame(
            '<svg width="1em" height="1em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            $this->view->render('<x-icon name="material-symbols:php" />'),
        );
    }

    #[Test]
    public function it_renders_a_debug_comment_in_local_env_when_icon_does_not_exist(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->once())
            ->method('get')
            ->with('https://api.iconify.design/material-symbols/php.svg')
            ->willReturn(new GenericResponse(status: Status::NOT_FOUND, body: ''));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);
        $this->container->singleton(Environment::class, Environment::LOCAL);

        $this->assertSame(
            '<!-- unknown-icon: material-symbols:php -->',
            $this->view->render('<x-icon name="material-symbols:php" />'),
        );
    }

    #[Test]
    public function it_renders_an_empty_string__in_non_local_env_when_icon_does_not_exist(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->once())
            ->method('get')
            ->with('https://api.iconify.design/material-symbols/php.svg')
            ->willReturn(new GenericResponse(status: Status::NOT_FOUND, body: ''));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);
        $this->container->singleton(Environment::class, Environment::PRODUCTION);

        $this->assertSame(
            '',
            $this->view->render('<x-icon name="material-symbols:php" />'),
        );
    }

    #[Test]
    public function it_forwards_the_class_attribute(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.design/material-symbols/php.svg')
            ->willReturn(new GenericResponse(
                status: Status::OK,
                body: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            ));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $this->assertSame(
            '<svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            $this->view->render(
                '<x-icon name="material-symbols:php" class="size-5" />',
            ),
        );
    }

    #[Test]
    public function it_forwards_the_style_attribute(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.design/material-symbols/php.svg')
            ->willReturn(new GenericResponse(
                status: Status::OK,
                body: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            ));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $this->assertSame(
            '<svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            $this->view->render(
                '<x-icon name="material-symbols:php" style="width: 24px; height: 24px;" />',
            ),
        );
    }

    #[Test]
    public function it_handles_width_and_height_together_props(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.design/material-symbols/php.svg')
            ->willReturn(new GenericResponse(
                status: Status::OK,
                body: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            ));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $this->assertSame(
            '<svg width="2em" height="2em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            $this->view->render(
                '<x-icon name="material-symbols:php" width="2em" height="2em" />',
            ),
        );
    }

    #[Test]
    public function fallback_dimensions_when_none_defined_in_any_supported_method(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.design/material-symbols/php.svg')
            ->willReturn(new GenericResponse(
                status: Status::OK,
                body: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            ));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $this->assertSame(
            '<svg width="1em" height="1em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            $this->view->render(
                '<x-icon name="material-symbols:php" />',
            ),
        );
    }

    #[Test]
    public function with_dynamic_data(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.design/material-symbols/php.svg')
            ->willReturn(new GenericResponse(
                status: Status::OK,
                body: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            ));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $rendered = $this->view->render(
            '<x-icon :name="$iconName" class="size-5" />',
            iconName: 'material-symbols:php',
        );

        $this->assertSame(
            '<svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            $rendered,
        );
    }

    #[Test]
    public function icon_renders_inside_named_slot_in_a_layout(): void
    {
        $this->view->registerViewComponent('x-test-layout', '<x-index><div><x-slot name="icon" /></div><x-slot /></x-index>');

        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.design/material-symbols/php.svg')
            ->willReturn(new GenericResponse(
                status: Status::OK,
                body: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg>',
            ));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $view = view(__DIR__ . '/../../../Fixtures/Views/view-with-icon-inside-named-slot.view.php');
        $html = $this->view->render($view);

        $this->assertSnippetsMatch(
            '<html lang="en"><head><title></title></head><body><div><svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M3 15V9h3.5q.6 0 1.05.45T8 10.5v1q0 .6-.45 1.05T6.5 13h-2v2zm6.5 0V9H11v2h2V9h1.5v6H13v-2.5h-2V15zm7 0V9H20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05T20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"/></svg></div>Test</body></html>',
            $html,
        );
    }
}
