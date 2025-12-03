<?php

declare(strict_types=1);

use Tempest\Auth\OAuth\Config\GitHubOAuthConfig;
use Tempest\Auth\OAuth\SupportedOAuthProvider;

use function Tempest\env;

return new GitHubOAuthConfig(
    clientId: env('OAUTH_GITHUB_CLIENT_ID') ?? '',
    clientSecret: env('OAUTH_GITHUB_CLIENT_SECRET') ?? '',
    redirectTo: [\Tempest\Auth\Installer\oauth\OAuthControllerStub::class, 'callback'],
    tag: SupportedOAuthProvider::GITHUB,
);
