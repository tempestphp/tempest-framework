<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use function Tempest\view;
use Tempest\View\View;

final readonly class StaticPageController
{
    #[StaticPage(StaticDataProvider::class)]
    #[Get('/static/{foo}/{bar}')]
    public function __invoke(string $foo, string $bar): View
    {
        return view(__DIR__ . '/../Views/static.view.php')->data(foo: $foo, bar: $bar);
    }
}
