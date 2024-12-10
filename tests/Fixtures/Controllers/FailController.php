<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Exception;
use Tempest\Router\Get;
use Tempest\Router\Request;
use Tempest\Router\Responses\Ok;

final readonly class FailController
{
    #[Get('/fail')]
    public function __invoke(Request $request): void
    {
        if ($request->get('error')) {
            trigger_error('Error message', E_USER_ERROR);
        } else {
            throw new Exception('Hi!');
        }
    }

    #[Get('/warning')]
    public function warning(): Ok
    {
        trigger_error('warning', E_USER_WARNING);

        return new Ok('ok');
    }
}
