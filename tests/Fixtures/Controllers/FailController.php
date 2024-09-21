<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Exception;
use Tempest\Http\Get;
use Tempest\Http\Responses\Ok;

final readonly class FailController
{
    #[Get('/fail')]
    public function __invoke(): void
    {
        throw new Exception('Hi!');
    }

    #[Get('/warning')]
    public function warning(): Ok
    {
        trigger_error('warning', E_USER_WARNING);

        return new Ok('ok');
    }
}
