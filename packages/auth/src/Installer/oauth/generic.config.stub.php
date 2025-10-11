<?php

declare(strict_types=1);

use Tempest\Auth\OAuth\Config\GenericOAuthConfig;
use Tempest\Auth\OAuth\SupportedOAuthProvider;

use function Tempest\env;

return new GenericOAuthConfig(
    clientId: env('OAUTH_GENERIC_CLIENT_ID') ?? '',
    clientSecret: env('OAUTH_GENERIC_CLIENT_SECRET') ?? '',
    redirectTo: [\Tempest\Auth\Installer\oauth\OAuthControllerStub::class, 'callback'],
    urlAuthorize: env('OAUTH_GENERIC_URL_AUTHORIZE') ?? '',
    urlAccessToken: env('OAUTH_GENERIC_URL_ACCESS_TOKEN') ?? '',
    urlResourceOwnerDetails: env('OAUTH_GENERIC_URL_RESOURCE_OWNER_DETAILS') ?? '',
    tag: SupportedOAuthProvider::GENERIC,
);
