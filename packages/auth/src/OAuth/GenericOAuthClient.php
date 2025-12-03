<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use BackedEnum;
use Closure;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Exceptions\OAuthStateWasInvalid;
use Tempest\Auth\Exceptions\OAuthTokenCouldNotBeRetrieved;
use Tempest\Auth\Exceptions\OAuthUserCouldNotBeRetrieved;
use Tempest\Http\Request;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\Session;
use Tempest\Mapper\ObjectFactory;
use Tempest\Router\UriGenerator;
use UnitEnum;

final class GenericOAuthClient implements OAuthClient
{
    private AbstractProvider $provider;

    public function __construct(
        private(set) readonly OAuthConfig $config,
        private readonly UriGenerator $uri,
        private readonly ObjectFactory $factory,
        private readonly Session $session,
        private readonly Authenticator $authenticator,
        ?AbstractProvider $provider = null,
    ) {
        $this->provider = $provider ?? $this->config->createProvider();
    }

    public string $sessionKey {
        get {
            $tag = $this->config->tag;

            $key = match (true) {
                is_string($tag) => $tag,
                $tag instanceof BackedEnum => $tag->value,
                $tag instanceof UnitEnum => $tag->name,
                default => 'default',
            };

            return "oauth:{$key}";
        }
    }

    public function buildAuthorizationUrl(array $scopes = [], array $options = []): string
    {
        return $this->provider->getAuthorizationUrl([
            'scope' => $scopes ?? $this->config->scopes,
            'redirect_uri' => $this->uri->createUri($this->config->redirectTo),
            ...$options,
        ]);
    }

    public function createRedirect(array $scopes = [], array $options = []): Redirect
    {
        $to = $this->buildAuthorizationUrl($scopes, $options);

        $this->session->set($this->sessionKey, $this->provider->getState());

        return new Redirect($to);
    }

    public function getState(): ?string
    {
        return $this->provider->getState();
    }

    public function requestAccessToken(string $code): AccessToken
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

    public function fetchUser(AccessToken $token): OAuthUser
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

    public function authenticate(Request $request, Closure $map): Authenticatable
    {
        $expectedState = $this->session->get($this->sessionKey);
        $actualState = $request->get('state');

        $this->session->remove($this->sessionKey);

        if ($expectedState !== $actualState) {
            throw new OAuthStateWasInvalid();
        }

        $user = $this->fetchUser(
            token: $this->requestAccessToken(
                code: $request->get('code'),
            ),
        );

        $authenticable = $map($user);

        $this->authenticator->authenticate($authenticable);

        return $authenticable;
    }
}
