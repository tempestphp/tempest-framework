<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Get;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;

final readonly class ControllerWithEnumBinding
{
    #[Get('/with-enum/{input}')]
    public function __invoke(EnumForController $input): Response
    {
        return new Ok();
    }
}
