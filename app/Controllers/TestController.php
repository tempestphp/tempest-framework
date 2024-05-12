<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Views\ViewModelWithResponseData;
use Tempest\Http\Get;
use Tempest\Http\Response;
use Tempest\Http\Status;
use function Tempest\response;
use function Tempest\view;
use Tempest\View\View;

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

    #[Get(uri: '/not-found')]
    public function notFound(): Response
    {
        return response(
            body: 'Not Found Test',
            status: Status::NOT_FOUND,
        );
    }

    #[Get(uri: '/server-error')]
    public function serverError(): Response
    {
        return response(
            body: 'Server Error Test',
            status: Status::INTERNAL_SERVER_ERROR,
        );
    }

    #[Get(uri: '/with-middleware', middleware: [
        TestMiddleware::class,
    ])]
    public function withMiddleware(): Response
    {
        return response()->ok();
    }

    #[Get('/view-model-with-response-data')]
    public function viewModelWithResponseData(): Response
    {
        return response()
            ->addHeader('x-from-viewmodel', 'true')
            ->setStatus(Status::CREATED)
            ->setView(new ViewModelWithResponseData());
    }
}
