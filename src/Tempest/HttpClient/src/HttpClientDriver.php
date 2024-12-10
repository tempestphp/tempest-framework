<?php

declare(strict_types=1);

namespace Tempest\HttpClient;

use Tempest\Router\Request;
use Tempest\Router\Response;

/**
 * The Tempest HttpClientDriver takes a Tempest request and
 * produces a Tempest response.
 */
interface HttpClientDriver
{
    public function send(Request $request): Response;
}
