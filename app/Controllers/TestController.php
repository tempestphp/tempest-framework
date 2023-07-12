<?php

namespace App\Controllers;

use Tempest\Http\Get;
use Tempest\Interfaces\Response;
use Tempest\Interfaces\View;

final readonly class TestController
{
    #[Get(uri: '/test/{id}/{name}')]
    public function withParams(int $id, string $name): Response
    {
        return response($id . $name);
    }

    #[Get(uri: '/test')]
    public function __invoke(): Response
    {
        return response('test');
    }

    #[Get(uri: '/view')]
    public function withView(): View
    {
        return view('Views/overview.php')->data(
            name: 'Brent',
        );
    }
}
