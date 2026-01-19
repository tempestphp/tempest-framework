<?php

declare(strict_types=1);

use Tempest\Auth\OAuth\Config\TwitchOAuthConfig;
use Tempest\Auth\OAuth\SupportedOAuthProvider;

use function Tempest\env;

return new TwitchOAuthConfig(
    clientId: env('OAUTH_TWITCH_CLIENT_ID') ?? '',
    clientSecret: env('OAUTH_TWITCH_CLIENT_SECRET') ?? '',
    redirectTo: [\Tempest\Auth\Installer\oauth\OAuthControllerStub::class, 'callback'],
    tag: SupportedOAuthProvider::TWITCH,
);