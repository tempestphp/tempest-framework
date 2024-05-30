<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Form;

use Tempest\Http\Get;
use Tempest\Http\Post;
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
    public function store(FormRequest $request): void
    {
    }
}
