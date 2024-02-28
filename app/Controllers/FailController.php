<?php

declare(strict_types=1);

namespace App\Controllers;

use Exception;
use Tempest\Http\Get;

final readonly class FailController
{
    #[Get('/fail')]
    public function __invoke()
    {
        throw new Exception('Hi!');
    }
}
