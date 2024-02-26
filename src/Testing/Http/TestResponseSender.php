<?php

declare(strict_types=1);

namespace Tempest\Testing\Http;

use Tempest\Container\InitializedBy;
use Tempest\Http\Response;
use Tempest\Http\ResponseSender;

#[InitializedBy(TestResponseSenderInitializer::class)]
final class TestResponseSender implements ResponseSender
{
    public function send(Response $response): Response
    {
        return new TestResponse($response);
    }
}
