<?php

declare(strict_types=1);

namespace Tempest\HttpClient;

use Tempest\Http\Request;
use Tempest\Http\Response;

/**
 * The Tempest HttpClientDriver takes a Tempest request and
 * produces a Tempest response.
 */
interface HttpClientDriver
{
    public function send(Request $request): Response;
}
