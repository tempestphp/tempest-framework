<?php

declare(strict_types=1);

use Tempest\Auth\OAuth\Config\FacebookOAuthConfig;
use Tempest\Auth\OAuth\SupportedOAuthProvider;

use function Tempest\env;

return new FacebookOAuthConfig(
    clientId: env('OAUTH_FACEBOOK_CLIENT_ID') ?? '',
    clientSecret: env('OAUTH_FACEBOOK_CLIENT_SECRET') ?? '',
    redirectTo: [\Tempest\Auth\Installer\oauth\OAuthControllerStub::class, 'callback'],
    tag: SupportedOAuthProvider::FACEBOOK,
);
