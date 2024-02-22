<?php

declare(strict_types=1);

namespace Tempest\Http;

interface ResponseSender
{
    public function send(Response $response): Response;
}
