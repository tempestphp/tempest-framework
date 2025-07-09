<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use Tempest\Container\Inject;
use Tempest\HttpClient\HttpClient;

trait IsOAuth2Provider
{
    #[Inject]
    private readonly HttpClient $httpClient;

    public private(set) string $scopeSeparator = ' ';
}
