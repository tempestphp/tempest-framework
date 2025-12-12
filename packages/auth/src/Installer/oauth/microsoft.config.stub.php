<?php

declare(strict_types=1);

use Tempest\Auth\OAuth\Config\MicrosoftOAuthConfig;
use Tempest\Auth\OAuth\SupportedOAuthProvider;

use function Tempest\env;

return new MicrosoftOAuthConfig(
    clientId: env('OAUTH_MICROSOFT_CLIENT_ID') ?? '',
    clientSecret: env('OAUTH_MICROSOFT_CLIENT_SECRET') ?? '',
    redirectTo: [\Tempest\Auth\Installer\oauth\OAuthControllerStub::class, 'callback'],
    tag: SupportedOAuthProvider::MICROSOFT,
);
