<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Http\Response;

interface ResponseSender
{
    public function send(Response $response): Response;
}
