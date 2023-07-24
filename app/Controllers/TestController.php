<?php

declare(strict_types=1);

namespace App\Controllers;

use Tempest\Http\Get;
use Tempest\Interface\Response;
use Tempest\Interface\View;

use function Tempest\response;
use function Tempest\view;

final readonly class TestController
{
    #[Get(uri: '/test/{id}/{name}')]
    public function withParams(string $id, string $name): Response
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

    #[Get(uri: '/redirect')]
    public function redirect(): Response
    {
        return response()->redirect('/');
    }
}
