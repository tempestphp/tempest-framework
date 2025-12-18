<?php

namespace Tests\Tempest\Integration\Route\Fixtures;

use Exception;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Exceptions\ConvertsToResponse;

/**
 * Used by RouterTest.
 */
final class ExceptionThatConvertsToRedirectResponse extends Exception implements ConvertsToResponse
{
    public function convertToResponse(): Response
    {
        return new Redirect('https://tempestphp.com');
    }
}
