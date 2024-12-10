<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Get;
use Tempest\Router\Response;
use Tempest\Router\Responses\Ok;

final readonly class ControllerWithEnumBinding
{
    #[Get('/with-enum/{input}')]
    public function __invoke(EnumForController $input): Response
    {
        return new Ok();
    }
}
