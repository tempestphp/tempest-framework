<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Exception;
use Tempest\Http\Request;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;

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
