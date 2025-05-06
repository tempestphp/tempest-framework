<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Static\Fixtures;

use Tempest\Http\Response;
use Tempest\Http\Responses\ServerError;
use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use Tempest\View\Exceptions\ViewCompilationError;
use Tempest\View\View;
use Tempest\Vite\Exceptions\ManifestNotFoundException;

use function Tempest\uri;
use function Tempest\view;

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
        string $foo, // @mago-expect best-practices/no-unused-parameter
        string $bar, // @mago-expect best-practices/no-unused-parameter
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
        string $foo, // @mago-expect best-practices/no-unused-parameter
        string $bar, // @mago-expect best-practices/no-unused-parameter
    ): void {
        throw new ViewCompilationError('view.php', '', new ManifestNotFoundException('fake-manifest.json'));
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
}
