<?php

declare(strict_types=1);

namespace Tempest\Router;

interface ResponseSender
{
    public function send(Response $response): Response;
}
