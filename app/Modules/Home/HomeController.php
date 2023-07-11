<?php

namespace App\Modules\Home;

use Tempest\Http\Get;

final readonly class HomeController
{
    #[Get(uri: '/')]
    public function __invoke()
    {
    }
}
