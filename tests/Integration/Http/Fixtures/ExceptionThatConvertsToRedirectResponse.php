<?php

namespace Tests\Tempest\Integration\Http\Fixtures;

use Exception;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Exceptions\ConvertsToResponse;

/**
 * Used by HttpExceptionHandlerTest.
 */
final class ExceptionThatConvertsToRedirectResponse extends Exception implements ConvertsToResponse
{
    public function convertToResponse(): Response
    {
        return new Redirect('https://tempestphp.com');
    }
}
