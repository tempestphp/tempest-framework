<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Get;
use function Tempest\view;
use Tempest\View\View;

final readonly class FormController
{
    #[Get('/form-test')]
    public function index(): View
    {
        return view('Views/form.php');
    }
}
