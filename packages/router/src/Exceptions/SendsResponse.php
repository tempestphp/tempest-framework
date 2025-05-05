<?php

namespace Tempest\Router\Exceptions;

use Tempest\Http\Response;

interface SendsResponse
{
    /**
     * Gets a response to be sent to the client.
     */
    public function toResponse(): Response;
}
