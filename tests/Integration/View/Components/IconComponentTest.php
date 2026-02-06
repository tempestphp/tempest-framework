<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View\Components;

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

    public function test_it_renders_an_icon(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->once())
            ->method('get')
            ->with('https://api.iconify.design/ph/eye.svg')
            ->willReturn(new GenericResponse(status: Status::OK, body: '<svg></svg>'));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $this->assertSame(
            '<svg></svg>',
            $this->view->render('<x-icon name="ph:eye" />'),
        );
    }

    public function test_it_downloads_the_icon_from_a_custom_api(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.test/ph/eye.svg')
            ->willReturn(new GenericResponse(status: Status::OK, body: '<svg></svg>'));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $this->container->singleton(
            IconConfig::class,
            fn () => new IconConfig(iconifyApiUrl: 'https://api.iconify.test', retryAfter: Duration::hours(12)),
        );

        $this->assertSame(
            '<svg></svg>',
            $this->view->render('<x-icon name="ph:eye" />'),
        );
    }

    public function test_fallback_without_name(): void
    {
        $this->assertSame(
            '',
            $this->view->render('<x-icon />'),
        );
    }

    public function test_it_caches_icons_on_the_first_render(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->once())
            ->method('get')
            ->with('https://api.iconify.design/ph/eye.svg')
            ->willReturn(new GenericResponse(status: Status::OK, body: '<svg></svg>'));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $this->view->render('<x-icon name="ph:eye" />');

        $iconCache = $this->container->get(IconCache::class);
        $cachedIcon = $iconCache->get('icon-ph-eye');

        $this->assertNotNull($cachedIcon);
        $this->assertSame('<svg></svg>', $cachedIcon);
    }

    public function test_it_renders_an_icon_from_cache(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.design/ph/eye.svg')
            ->willReturn(new GenericResponse(status: Status::OK, body: '<svg></svg>'));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        // Trigger first render, which should cache the icon
        $this->view->render('<x-icon name="ph:eye" />');

        $this->assertSame(
            '<svg></svg>',
            $this->view->render('<x-icon name="ph:eye" />'),
        );
    }

    public function test_it_renders_a_debug_comment_in_local_env_when_icon_does_not_exist(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->once())
            ->method('get')
            ->with('https://api.iconify.design/ph/eye.svg')
            ->willReturn(new GenericResponse(status: Status::NOT_FOUND, body: ''));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);
        $this->container->singleton(Environment::class, Environment::LOCAL);

        $this->assertSame(
            '<!-- unknown-icon: ph:eye -->',
            $this->view->render('<x-icon name="ph:eye" />'),
        );
    }

    public function test_it_renders_an_empty_string__in_non_local_env_when_icon_does_not_exist(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->once())
            ->method('get')
            ->with('https://api.iconify.design/ph/eye.svg')
            ->willReturn(new GenericResponse(status: Status::NOT_FOUND, body: ''));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);
        $this->container->singleton(Environment::class, Environment::PRODUCTION);

        $this->assertSame(
            '',
            $this->view->render('<x-icon name="ph:eye" />'),
        );
    }

    public function test_it_forwards_the_class_attribute(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.design/ph/eye.svg')
            ->willReturn(new GenericResponse(status: Status::OK, body: '<svg></svg>'));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $this->assertSame(
            '<svg class="size-5"></svg>',
            $this->view->render(
                '<x-icon name="ph:eye" class="size-5" />',
            ),
        );
    }

    public function test_with_dynamic_data(): void
    {
        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.design/ph/eye.svg')
            ->willReturn(new GenericResponse(status: Status::OK, body: '<svg></svg>'));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $rendered = $this->view->render(
            '<x-icon :name="$iconName" class="size-5" />',
            iconName: 'ph:eye',
        );

        $this->assertSame(
            '<svg class="size-5"></svg>',
            $rendered,
        );
    }

    public function test_icon_renders_inside_named_slot_in_a_layout(): void
    {
        $this->view->registerViewComponent('x-test-layout', '<x-index><div><x-slot name="icon" /></div><x-slot /></x-index>');

        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.design/ph/eye.svg')
            ->willReturn(new GenericResponse(status: Status::OK, body: '<svg></svg>'));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $view = view(__DIR__ . '/../../../Fixtures/Views/view-with-icon-inside-named-slot.view.php');
        $html = $this->view->render($view);

        $this->assertSnippetsMatch(
            '<html lang="en"><head><title></title></head><body><div><svg class="size-5"></svg></div>Test</body></html>',
            $html,
        );
    }
}
