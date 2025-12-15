<?php

declare(strict_types=1);

namespace Tempest\Router\Exceptions;

use Tempest\Http\Request;
use Tempest\Http\Response;
use Throwable;

/**
 * Responsible for rendering exceptions as HTTP responses.
 */
interface ExceptionRenderer
{
    /**
     * Determines if this renderer can handle the given exception for the request.
     */
    public function canRender(Throwable $throwable, Request $request): bool;

    /**
     * Renders the exception as an HTTP response.
     */
    public function render(Throwable $throwable): Response;
}
