<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\InitializedBy;

#[InitializedBy(ResponseSenderInitializer::class)]
interface ResponseSender
{
    public function send(Response $response): Response;
}
