<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Get;
use Tempest\View\View;

use function Tempest\View\view;

final readonly class FormController
{
    #[Get('/form-test')]
    public function index(): View
    {
        return view('Views/form.php');
    }
}
