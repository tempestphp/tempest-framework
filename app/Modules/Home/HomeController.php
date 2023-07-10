<?php

namespace App\Modules\Home;

use Tempest\Route\Get;

final readonly class HomeController
{
    #[Get(uri: '/')]
    public function __invoke() {}
}