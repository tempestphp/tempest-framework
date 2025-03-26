<?php

declare(strict_types=1);

namespace Tempest\Router;

interface ResponseProcessor
{
    public function process(Response $response): Response;
}
