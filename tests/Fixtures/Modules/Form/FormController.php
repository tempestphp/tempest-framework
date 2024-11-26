<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Form;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\Post;
use function Tempest\view;
use Tempest\View\View;

final readonly class FormController
{
    #[Get('/form')]
    public function index(): View
    {
        return view(__DIR__ . '/../../Modules/Form/form.view.php');
    }

    #[Post('/form/store')]
    public function store(FormRequest $request): Response
    {
        return new Ok('Ok!');
    }
}
