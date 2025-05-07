<?php

namespace Tests\Tempest\Integration\Route\Fixtures;

use Exception;
use Tempest\Http\Responses\ServerError;

final class Http500Controller
{
    public function basic500(): string
    {
        throw new Exception('oops');
    }

    public function custom500(): ServerError
    {
        return new ServerError('internal server error');
    }
}
