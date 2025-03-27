<?php

namespace Tests\Tempest\Integration\Route;

use Tempest\Router\Response;
use Tempest\Router\ResponseProcessor;

final readonly class TestProcessedResponseProcessor implements ResponseProcessor
{
    public function process(Response $response): Response
    {
        return $response->addHeader('X-Processed', 'true');
    }
}
