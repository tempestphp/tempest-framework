<?php

namespace Tests\Tempest\Integration\Http\Fixtures;

use Exception;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Exceptions\SendsResponse;

/**
 * Used by HttpExceptionHandlerTest.
 */
final class ExceptionThatSendsRedirectResponse extends Exception implements SendsResponse
{
    public function toResponse(): Response
    {
        return new Redirect('https://tempestphp.com');
    }
}
