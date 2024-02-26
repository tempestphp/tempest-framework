<?php

declare(strict_types=1);

namespace Tempest\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClient extends ClientInterface
{
    public function get(string $uri, array $headers = []): ResponseInterface;

    public function post(string $uri, string $content, array $headers = []): ResponseInterface;
}
