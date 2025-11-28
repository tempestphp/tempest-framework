<?php

declare(strict_types=1);

use Tempest\Auth\OAuth\Config\LinkedInOAuthConfig;
use Tempest\Auth\OAuth\SupportedOAuthProvider;

use function Tempest\env;

return new LinkedInOAuthConfig(
    clientId: env('OAUTH_LINKEDIN_CLIENT_ID') ?? '',
    clientSecret: env('OAUTH_LINKEDIN_CLIENT_SECRET') ?? '',
    redirectTo: [\Tempest\Auth\Installer\oauth\OAuthControllerStub::class, 'callback'],
    tag: SupportedOAuthProvider::LINKEDIN,
);
