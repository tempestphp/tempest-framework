<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Views\ViewModelWithResponseData;
use Tempest\Http\Get;
use Tempest\Http\Response;
use Tempest\Http\Responses\Created;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Responses\ServerError;
use function Tempest\view;
use Tempest\View\View;

final readonly class TestController
{
    #[Get(uri: '/test/{id}/{name}')]
    public function withParams(string $id, string $name): Response
    {
        return new Ok($id . $name);
    }

    #[Get(uri: '/test')]
    public function __invoke(): Response
    {
        return new Ok('test');
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
        return new Redirect('/');
    }

    #[Get(uri: '/not-found')]
    public function notFound(): Response
    {
        return new NotFound('Not Found Test');
    }

    #[Get(uri: '/server-error')]
    public function serverError(): Response
    {
        return new ServerError('Server Error Test');
    }

    #[Get(uri: '/with-middleware', middleware: [
        TestMiddleware::class,
    ])]
    public function withMiddleware(): Response
    {
        return new Ok();
    }

    #[Get('/view-model-with-response-data')]
    public function viewModelWithResponseData(): Response
    {
        return (new Created(new ViewModelWithResponseData()))
            ->addHeader('x-from-viewmodel', 'true');
    }
}
