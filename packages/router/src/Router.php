<?php

declare(strict_types=1);

namespace Tempest\Router;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\Request;
use Tempest\Http\Response;

interface Router
{
    public function dispatch(Request|PsrRequest $request): Response;
}
