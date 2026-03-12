<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Config;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Tempest\Auth\OAuth\OAuthConfig;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Mapper\ObjectFactory;
use UnitEnum;
use Vertisan\OAuth2\Client\Provider\TwitchHelix;
use Vertisan\OAuth2\Client\Provider\TwitchHelixResourceOwner;

final class TwitchOAuthConfig implements OAuthConfig
{
    public string $provider = TwitchHelix::class;

    public function __construct(
        /**
         * The client ID for the Twitch OAuth application.
         */
        public string $clientId,

        /**
         * The client secret for the Twitch OAuth application.
         */
        public string $clientSecret,

        /**
         * The controller action to redirect to after the user authorizes the application.
         */
        public string|array $redirectTo,

        /**
         * The scopes to request from Twitch.
         *
         * @var string[]
         */
        public array $scopes = ['user:read:email'],

        /**
         * Identifier for this OAuth configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createProvider(): AbstractProvider
    {
        return new TwitchHelix([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $this->redirectTo,
        ]);
    }

    /**
     * @param TwitchHelixResourceOwner $resourceOwner
     */
    public function mapUser(ObjectFactory $factory, ResourceOwnerInterface $resourceOwner): OAuthUser
    {
        return $factory->withData([
            'id' => (string) $resourceOwner->getId(),
            'email' => $resourceOwner->getEmail(),
            'name' => $resourceOwner->getDisplayName(),
            'nickname' => $resourceOwner->getDisplayName(),
            'avatar' => $resourceOwner->getProfileImageUrl(),
            'provider' => $this->provider,
            'raw' => $resourceOwner->toArray(),
        ])->to(OAuthUser::class);
    }
}
