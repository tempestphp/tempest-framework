<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Config;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Tempest\Auth\OAuth\OAuthConfig;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Mapper\ObjectFactory;
use UnitEnum;

final class GoogleOAuthConfig implements OAuthConfig
{
    public string $provider = Google::class;

    public function __construct(
        /**
         * The client ID for the Google OAuth application.
         */
        public string $clientId,

        /**
         * The client secret for the Google OAuth application.
         */
        public string $clientSecret,

        /**
         * The redirect URI for the OAuth flow.
         */
        public string|array $redirectUri,

        /**
         * The scopes to request from Google.
         *
         * @var string[]
         */
        public array $scopes = ['openid', 'email', 'profile'],

        /**
         * Identifier for this OAuth configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createProvider(): AbstractProvider
    {
        return new Google([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
        ]);
    }

    /**
     * @param GoogleUser $resourceOwner
     */
    public function mapUser(ObjectFactory $factory, ResourceOwnerInterface $resourceOwner): OAuthUser
    {
        return $factory->withData([
            'id' => (string) $resourceOwner->getId(),
            'email' => $resourceOwner->getEmail(),
            'name' => $resourceOwner->getName(),
            'nickname' => $resourceOwner->getFirstName(),
            'avatar' => $resourceOwner->getAvatar(),
            'provider' => $this->provider,
            'raw' => $resourceOwner->toArray(),
        ])->to(OAuthUser::class);
    }
}
