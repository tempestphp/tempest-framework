<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\League;

use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use Tempest\Auth\OAuth\DataObjects\AccessToken;
use Tempest\Auth\OAuth\DataObjects\OAuthUserData;
use Tempest\Auth\OAuth\Exceptions\OAuthException;
use Tempest\Http\Session\Session;
use function Tempest\Support\str;

final class OAuthGenericProvider
{
    private GenericProvider $internalProvider;

    public private(set) string $clientId;
    public private(set) string $clientSecret;
    public private(set) array $defaultScopes;
    public private(set) string $redirectUri;
    public private(set) string $authorizeEndpoint;
    public private(set) string $accessTokenEndpoint;
    public private(set) string $userDataEndpoint;
    public private(set) string $stateSessionSlug;

    public function __construct(
        private readonly Session $session
    ) {}

    /**
     * @param array<string> $defaultScopes
     */
    public function configure(
       string $clientId,
       string $clientSecret,
       array $defaultScopes,
       string $redirectUri,
       string $authorizeEndpoint,
       string $accessTokenEndpoint,
       string $userDataEndpoint,
       string $stateSessionSlug = 'oauth-state',
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->defaultScopes = $defaultScopes;
        $this->redirectUri = $redirectUri;
        $this->authorizeEndpoint = $authorizeEndpoint;
        $this->accessTokenEndpoint = $accessTokenEndpoint;
        $this->userDataEndpoint = $userDataEndpoint;
        $this->stateSessionSlug = $stateSessionSlug;

        $this->internalProvider = new GenericProvider([
            'clientId'                => $this->clientId,
            'clientSecret'            => $this->clientSecret,
            'redirectUri'             => $this->redirectUri,
            'urlAuthorize'            => $this->authorizeEndpoint,
            'urlAccessToken'          => $this->accessTokenEndpoint,
            'urlResourceOwnerDetails' => $this->userDataEndpoint,
        ]);
    }

    /**
     * @param array<string, mixed>|null $additionalParameters Additional parameters to include in the authorization URL.
     * @param array<string>|null $scopes Scopes to request. If null, the default scopes will be used.
     * @param string|null $state A state parameter to include in the authorization URL. If null, a random state will be generated.
     */
    public function generateAuthorizationUrl(
        array $additionalParameters = [],
        ?array $scopes = null,
        ?string $state = null,
        ?string $scopeSeparator = ' ',
    ): string
    {
        $scopes ??= $this->defaultScopes;
        $state ??= $this->generateState();

        $this->session->flash($this->stateSessionSlug, $state);

        return $this->internalProvider->getAuthorizationUrl([
            'scope' => implode($scopeSeparator, $scopes),
            'state' => $state,
            ...$additionalParameters
        ]);
    }

    /**
     * @param array<string, mixed>|null $additionalParameters Additional parameters to include in the request.
     */
    public function generateAccessToken(
        string $code,
        array $additionalParameters = [],
    ): AccessToken
    {
        try {
            $token = $this->internalProvider->getAccessToken(
                grant: 'authorization_code',
                options: [
                    'code' => $code,
                    ...$additionalParameters
                ]
            );

            return AccessToken::fromLeagueAccessToken($token);
        } catch (GuzzleException|IdentityProviderException $e) {
            throw new OAuthException('Failed to get access token: ' . $e->getMessage(), previous: $e);
        }
    }

    public function fetchUserDataFromToken(
        AccessToken $accessToken
    ): OAuthUserData
    {
        try {
            return new OAuthUserData(
                id: '',
                nickname: '',
                name: '',
                email: '',
                avatar: '',
                rawData: $this->internalProvider->getResourceOwner($accessToken->toLeagueAccessToken())->toArray()
            );
        } catch (GuzzleException|IdentityProviderException $e) {
            throw new OAuthException('Failed to get user data: ' . $e->getMessage(), previous: $e);
        }
    }

    private function generateState(): string
    {
        return str()->random(40)->toString();
    }
}