<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\League;

final class OAuthClient
{
    public function __construct(
        public private(set) OAuthGenericProvider $provider,
    ) {}
}