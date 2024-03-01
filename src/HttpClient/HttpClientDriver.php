<?php

namespace Tempest\HttpClient;

use Tempest\Container\InitializedBy;
use Tempest\Http\Request;
use Tempest\Http\Response;

/**
 * The Tempest HttpClientDriver takes a Tempest request and
 * produces a Tempest response.
 */
#[InitializedBy(HttpClientInitializer::class)]
interface HttpClientDriver
{
    public function send(Request $request): Response;
}