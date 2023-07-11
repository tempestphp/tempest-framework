<?php

namespace App\Controllers;

use Tempest\Http\Get;
use Tempest\Http\Response;
use Tempest\View\GenericView;

final readonly class TestController
{
    #[Get(uri: '/test/{id}/{name}')]
    public function withParams(int $id, string $name): Response
    {
        return Response::ok($id . $name);
    }

    #[Get(uri: '/test')]
    public function __invoke(): Response
    {
        return Response::ok('test');
    }

    #[Get(uri: '/view')]
    public function withView(): GenericView
    {
        return GenericView::new(
            'Views/overview.php',
            name: 'Brent',
        );
    }
}