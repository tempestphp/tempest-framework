<?php

namespace App\Controllers;

use Tempest\Route\Get;

final readonly class HomeController
{
    #[Get(uri: '/')]
    public function __invoke() {}
}