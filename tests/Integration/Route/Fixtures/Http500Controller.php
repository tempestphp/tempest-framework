<?php

namespace Tests\Tempest\Integration\Route\Fixtures;

use Exception;
use Tempest\Http\Responses\ServerError;
use Tempest\Router\Get;

final class Http500Controller
{
    #[Get('/throws-exception')]
    public function throwsException(): string
    {
        throw new Exception('oops');
    }

    #[Get('/returns-server-error')]
    public function serverError(): ServerError
    {
        return new ServerError();
    }

    #[Get('/returns-server-error-with-body')]
    public function serverErrorWithBody(): ServerError
    {
        return new ServerError('custom error');
    }
}
