<?php

declare(strict_types=1);

namespace App\Modules\Home;

use Tempest\Http\Get;
use Tempest\Http\Request;
use function Tempest\lw;
use Tempest\View\View;

final readonly class HomeController
{
    #[Get(uri: '/')]
    public function __invoke(Request $request): View
    {
        $view = new HomeView(
            name: 'Brent',
        );

        //        lw($view);
        return $view;
    }
}
