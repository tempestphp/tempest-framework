<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Static\Fixtures;

use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Responses\ServerError;
use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use Tempest\View\Exceptions\ViewCompilationFailed;
use Tempest\View\View;
use Tempest\Vite\Exceptions\ManifestWasNotFound;

use function Tempest\Router\uri;
use function Tempest\View\view;

final readonly class StaticPageController
{
    #[Get('/static/{foo}/{bar}')]
    #[StaticPage(StaticDataProvider::class)]
    public function __invoke(string $foo, string $bar): View
    {
        return view(__DIR__ . '/static.view.php')->data(foo: $foo, bar: $bar);
    }

    #[Get('/static/http500/{foo}/{bar}')]
    #[StaticPage(StaticDataProvider::class)]
    public function http500(
        string $foo,
        string $bar,
    ): Response {
        return new ServerError();
    }

    #[Get('/static/no-content/{foo}/{bar}')]
    #[StaticPage(StaticDataProvider::class)]
    public function noTextualContent(string $foo, string $bar): array
    {
        return [];
    }

    #[Get('/static/vite/{foo}/{bar}')]
    #[StaticPage(StaticDataProvider::class)]
    public function vite(
        string $foo,
        string $bar,
    ): void {
        throw new ViewCompilationFailed('view.php', '', new ManifestWasNotFound('fake-manifest.json'));
    }

    #[Get('/static/dead-link')]
    #[StaticPage]
    public function deadLink(): string
    {
        return implode(PHP_EOL, [
            sprintf('<a href="%s">foo</a>', uri('/404')),
            '<a href="https://google.com/404">foo</a>',
        ]);
    }

    #[Get('/static/redirecting-route')]
    #[StaticPage]
    public function hasRedirect(): string
    {
        return sprintf('<a href="%s">foo</a>', uri('/redirecting-route'));
    }

    #[Get('/redirecting-route')]
    public function redirectingRoute(): Redirect
    {
        return new Redirect('https://google.com');
    }

    #[Get('/static/allowed-dead-link')]
    #[StaticPage]
    public function allowedDeadLink(): string
    {
        return sprintf('<a ssg-ignore href="%s">foo</a>', uri('/404'));
    }
}
