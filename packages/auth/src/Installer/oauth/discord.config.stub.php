<?php

declare(strict_types=1);

use Tempest\Auth\OAuth\Config\DiscordOAuthConfig;
use Tempest\Auth\OAuth\SupportedOAuthProvider;

use function Tempest\env;

return new DiscordOAuthConfig(
    clientId: env('OAUTH_DISCORD_CLIENT_ID') ?? '',
    clientSecret: env('OAUTH_DISCORD_CLIENT_SECRET') ?? '',
    redirectTo: [\Tempest\Auth\Installer\oauth\OAuthControllerStub::class, 'callback'],
    tag: SupportedOAuthProvider::DISCORD,
);
