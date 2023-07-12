<?php

namespace App\Modules\Home;

use Tempest\Http\Get;
use Tempest\Interfaces\Response;

final readonly class HomeController
{
    #[Get(uri: '/')]
    public function __invoke(): Response
    {
        return response('Hello!');
    }
}
