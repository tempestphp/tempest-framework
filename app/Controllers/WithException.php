<?php

namespace App\Controllers;

use Exception;
use Tempest\Http\Get;

final readonly class WithException
{
    #[Get('/with-exception')]
    public function __invoke()
    {
        throw new Exception('Hi!');
    }
}