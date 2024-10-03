<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Form;

use Tempest\Http\Get;
use Tempest\Http\Post;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
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
