<?php

declare(strict_types=1);

namespace App\Modules\Form;

use Tempest\Http\Get;
use Tempest\Http\Post;
use Tempest\Http\Response;
use function Tempest\response;

final readonly class FormController
{
    #[Get('/form')]
    public function index(): Response
    {
        return response()->setView('Modules/Form/form.view.php');
    }

    #[Post('/form')]
    public function store(FormRequest $request): void
    {
    }
}
