<?php

declare(strict_types=1);

use Tempest\Auth\OAuth\Config\SlackOAuthConfig;
use Tempest\Auth\OAuth\SupportedOAuthProvider;

use function Tempest\env;

return new SlackOAuthConfig(
    clientId: env('OAUTH_SLACK_CLIENT_ID') ?? '',
    clientSecret: env('OAUTH_SLACK_CLIENT_SECRET') ?? '',
    redirectTo: [\Tempest\Auth\Installer\oauth\OAuthControllerStub::class, 'callback'],
    tag: SupportedOAuthProvider::SLACK,
);
