<?php

declare(strict_types=1);

use Tempest\Auth\OAuth\Config\AppleOAuthConfig;
use Tempest\Auth\OAuth\SupportedOAuthProvider;

use function Tempest\env;

return new AppleOAuthConfig(
    clientId: env('OAUTH_APPLE_CLIENT_ID') ?? '',
    teamId: env('OAUTH_APPLE_TEAM_ID') ?? '',
    keyId: env('OAUTH_APPLE_KEY_ID') ?? '',
    keyFile: env('OAUTH_APPLE_KEY_FILE') ?? '',
    redirectTo: [\Tempest\Auth\Installer\oauth\OAuthControllerStub::class, 'callback'],
    tag: SupportedOAuthProvider::APPLE,
);
