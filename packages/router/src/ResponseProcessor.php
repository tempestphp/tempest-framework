<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Http\Response;

interface ResponseProcessor
{
    public function process(Response $response): Response;
}
