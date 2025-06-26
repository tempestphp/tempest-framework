<?php

namespace Tests\Tempest\Integration\Route\Fixtures;

use Tempest\Router\Get;

final class ConvertsToResponseExceptionController
{
    #[Get('/converts-to-response-exception')]
    public function __invoke()
    {
        throw new ConvertsToResponseException('Test');
    }
}