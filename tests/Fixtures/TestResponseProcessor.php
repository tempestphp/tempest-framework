<?php

namespace Tests\Tempest\Fixtures;

use Tempest\Router\Request;
use Tempest\Router\Response;
use Tempest\Router\ResponseProcessor;

// Tests that response processors are discovered
final readonly class TestResponseProcessor implements ResponseProcessor
{
    public function __construct(
        private readonly Request $request,
    ) {}

    public function process(Response $response): Response
    {
        if ($this->request->headers->get('X-Processed')) {
            return $response->addHeader('X-Processed', 'true');
        }

        return $response;
    }
}
