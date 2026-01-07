<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Form;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;

use function Tempest\View\view;

final readonly class FormController
{
    #[Get('/form')]
    public function index(): View
    {
        return view(__DIR__ . '/../../Modules/Form/form.view.php');
    }

    #[Post('/form/store')]
    public function store(FormRequest $_request): Response
    {
        return new Ok('Ok!');
    }
}
