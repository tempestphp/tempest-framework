<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Tempest\Auth\Exceptions\OAuthTokenCouldNotBeRetrieved;
use Tempest\Auth\Exceptions\OAuthUserCouldNotBeRetrieved;
use Tempest\Mapper\ObjectFactory;
use Tempest\Router\UriGenerator;

final readonly class GenericOAuthClient implements OAuthClient
{
    private AbstractProvider $provider;

    public function __construct(
        private(set) OAuthConfig $config,
        private UriGenerator $uri,
        private ObjectFactory $factory,
        ?AbstractProvider $provider = null,
    ) {
        $this->provider = $provider ?? $this->config->createProvider();
    }

    public function getAuthorizationUrl(array $scopes = [], array $options = []): string
    {
        return $this->provider->getAuthorizationUrl([
            'scope' => $scopes ?? $this->config->scopes,
            'redirect_uri' => $this->uri->createUri($this->config->redirectTo),
            ...$options,
        ]);
    }

    public function getState(): ?string
    {
        return $this->provider->getState();
    }

    public function getAccessToken(string $code): AccessToken
    {
        try {
            return $this->provider->getAccessToken('authorization_code', [
                'code' => $code,
                'redirect_uri' => $this->uri->createUri($this->config->redirectTo),
            ]);
        } catch (IdentityProviderException $exception) {
            throw OAuthTokenCouldNotBeRetrieved::fromProviderException($exception);
        }
    }

    public function getUser(AccessToken $token): OAuthUser
    {
        try {
            return $this->config->mapUser(
                factory: $this->factory,
                resourceOwner: $this->provider->getResourceOwner($token),
            );
        } catch (IdentityProviderException $exception) {
            throw OAuthUserCouldNotBeRetrieved::fromProviderException($exception);
        }
    }

    public function fetchUser(string $code): OAuthUser
    {
        return $this->getUser(
            token: $this->getAccessToken($code),
        );
    }
}
