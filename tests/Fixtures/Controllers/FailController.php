<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Exception;
use Tempest\Http\Get;

final readonly class FailController
{
    #[Get('/fail')]
    public function __invoke(): void
    {
        throw new Exception('Hi!');
    }
}
