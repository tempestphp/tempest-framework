<?php

namespace Tempest\Router\Exceptions;

use Tempest\Http\Response;

/**
 * Marks this exception class as one that can be converted to a response.
 */
interface ConvertsToResponse extends \Throwable
{
    /**
     * Gets a response to be sent to the client.
     */
    public function convertToResponse(): Response;
}
