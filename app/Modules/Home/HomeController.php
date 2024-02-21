<?php

declare(strict_types=1);

namespace App\Modules\Home;

use Tempest\Http\Get;
use Tempest\View\View;

final readonly class HomeController
{
    #[Get(uri: '/')]
    public function __invoke(): View
    {
        return new HomeView(
            name: 'Brent',
        );
    }
}
