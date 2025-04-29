<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Tempest\Cache\IconCache;
use Tempest\Core\AppConfig;
use Tempest\Core\ConfigCache;
use Tempest\Core\Environment;
use Tempest\Http\GenericResponse;
use Tempest\Http\Status;
use Tempest\HttpClient\HttpClient;
use Tempest\View\IconConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\view;

final class IconComponentTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->get(IconCache::class)->clear();
        $this->container->get(ConfigCache::class)->clear();
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
            $this->render(
                '<x-icon name="ph:eye" />',
            ),
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
            fn () => new IconConfig(iconifyApiUrl: 'https://api.iconify.test'),
        );

        $this->assertSame(
            '<svg></svg>',
            $this->render(
                '<x-icon name="ph:eye" />',
            ),
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

        $this->render('<x-icon name="ph:eye" />');

        $iconCache = $this->container->get(IconCache::class);
        $cachedIcon = $iconCache?->get('iconify-ph-eye');

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
        $this->render('<x-icon name="ph:eye" />');

        $this->assertSame(
            '<svg></svg>',
            $this->render('<x-icon name="ph:eye" />'),
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
        $this->container->singleton(AppConfig::class, fn () => new AppConfig(environment: Environment::LOCAL));

        $this->assertSame(
            '<!-- unknown-icon: ph:eye -->',
            $this->render('<x-icon name="ph:eye" />'),
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
        $this->container->singleton(AppConfig::class, fn () => new AppConfig(environment: Environment::PRODUCTION));

        $this->assertSame(
            '',
            $this->render('<x-icon name="ph:eye" />'),
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
            $this->render(
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

        $rendered = $this->render(
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
        $this->registerViewComponent('x-test-layout', '<x-index><div><x-slot name="icon" /></div><x-slot /></x-index>');

        $mockHttpClient = $this->createMock(HttpClient::class);
        $mockHttpClient
            ->expects($this->exactly(1))
            ->method('get')
            ->with('https://api.iconify.design/ph/eye.svg')
            ->willReturn(new GenericResponse(status: Status::OK, body: '<svg></svg>'));

        $this->container->register(HttpClient::class, fn () => $mockHttpClient);

        $view = view(__DIR__ . '/../../Fixtures/Views/view-with-icon-inside-named-slot.view.php');
        $html = $this->render($view);

        $this->assertSnippetsMatch(
            '<html lang="en"><head><title></title></head><body><div><svg class="size-5"></svg></div>Test</body></html>',
            $html,
        );
    }
}
