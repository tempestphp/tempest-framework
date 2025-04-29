<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;

final readonly class ControllerWithEnumBinding
{
    #[Get('/with-enum/{input}')]
    public function __invoke(
        EnumForController $input, // @mago-expect best-practices/no-unused-parameter
    ): Response {
        return new Ok();
    }
}
