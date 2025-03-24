<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use Tempest\View\View;

use function Tempest\view;

final readonly class StaticPageController
{
    #[Get('/static/{foo}/{bar}')]
    #[StaticPage(StaticDataProvider::class)]
    public function __invoke(string $foo, string $bar): View
    {
        return view(__DIR__ . '/../Views/static.view.php')->data(foo: $foo, bar: $bar);
    }
}
