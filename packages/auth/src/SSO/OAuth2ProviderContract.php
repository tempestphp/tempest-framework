<?php

declare(strict_types=1);

namespace Tempest\Auth\SSO;

interface OAuth2ProviderContract extends OAuthProviderContract
{
//    public string $clientId {get;}

    public function getAuthorizationUrl(): string;
}