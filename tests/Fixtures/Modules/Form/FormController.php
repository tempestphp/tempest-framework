<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Form;

use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\Response;
use Tempest\Router\Responses\Ok;
use function Tempest\view;
use Tempest\View\View;

final readonly class FormController
{
    #[Get('/form')]
    public function index(): View
    {
        return view('Modules/Form/form.view.php');
    }

    #[Post('/form')]
    public function store(FormRequest $request): Response
    {
        return new Ok('Ok!');
    }
}
