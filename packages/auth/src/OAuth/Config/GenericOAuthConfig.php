<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Config;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Tempest\Auth\OAuth\OAuthConfig;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Mapper\ObjectFactory;
use UnitEnum;

final class GenericOAuthConfig implements OAuthConfig
{
    public string $provider = GenericProvider::class;

    public function __construct(
        /**
         * The client ID for the OAuth provider.
         */
        public string $clientId,

        /**
         * The client secret for the OAuth provider.
         */
        public string $clientSecret,

        /**
         * The controller action to redirect to after the user authorizes the application.
         */
        public string|array $redirectTo,

        /**
         * The authorization URL for the OAuth provider.
         */
        public string $urlAuthorize,

        /**
         * The access token URL for the OAuth provider.
         */
        public string $urlAccessToken,

        /**
         * The resource owner details URL for the OAuth provider.
         */
        public string $urlResourceOwnerDetails,

        /**
         * The scopes to request from the OAuth provider.
         *
         * @var string[]
         */
        public array $scopes = [],

        /**
         * Identifier for this OAuth configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createProvider(): AbstractProvider
    {
        return new GenericProvider([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'urlAuthorize' => $this->urlAuthorize,
            'urlAccessToken' => $this->urlAccessToken,
            'urlResourceOwnerDetails' => $this->urlResourceOwnerDetails,
        ]);
    }

    public function mapUser(ObjectFactory $factory, ResourceOwnerInterface $resourceOwner): OAuthUser
    {
        return $factory
            ->withData([
                'provider' => 'generic',
                'raw' => $data = $resourceOwner->toArray(),
                ...$data,
            ])
            ->to(OAuthUser::class);
    }
}
