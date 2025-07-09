<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

interface OAuth2ProviderContract extends OAuthProviderContract
{
    /**
     * @var array<string> The default scopes for the OAuth2 provider.
     */
    public array $scopes {get;}

    /**
     * @var string The URL to redirect the user for authorization.
     */
    public string $authorizationUrl {get;}

    /**
     * @var string The URL to exchange the authorization code for an access token.
     */
    public string $accessTokenUrl {get;}

    /**
     * @var string The URL to fetch user data after authorization.
     */
    public string $userDataUrl {get;}

//    public function getUserData(array $headers = []): OAuth2UserData;
}