<?php

declare(strict_types=1);

namespace Tempest\Auth\SSO;

interface OAuth2ProviderContract extends OAuthProviderContract
{
    public array $scopes {get;}

    public string $authorizationUrl {get;}

    public string $accessTokenUrl {get;}

    public function getAuthorizationUrl(): string;
}