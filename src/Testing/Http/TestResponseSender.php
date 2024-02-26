<?php

declare(strict_types=1);

namespace Tempest\Testing\Http;

use Tempest\Http\Response;
use Tempest\Http\ResponseSender;

final class TestResponseSender implements ResponseSender
{
    public function send(Response $response): Response
    {
        return new TestResponse($response);
    }
}
