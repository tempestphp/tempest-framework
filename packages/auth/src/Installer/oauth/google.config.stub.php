<?php

declare(strict_types=1);

use Tempest\Auth\OAuth\Config\GoogleOAuthConfig;
use Tempest\Auth\OAuth\SupportedOAuthProvider;

use function Tempest\env;

return new GoogleOAuthConfig(
    clientId: env('OAUTH_GOOGLE_CLIENT_ID') ?? '',
    clientSecret: env('OAUTH_GOOGLE_CLIENT_SECRET') ?? '',
    redirectTo: [\Tempest\Auth\Installer\oauth\OAuthControllerStub::class, 'callback'],
    tag: SupportedOAuthProvider::GOOGLE,
);
