<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Config;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Instagram;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Tempest\Auth\OAuth\OAuthConfig;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Mapper\ObjectFactory;
use UnitEnum;

final class InstagramOAuthConfig implements OAuthConfig
{
    public string $provider = Instagram::class;

    public function __construct(
        /**
         * The client ID for the Instagram OAuth application.
         */
        public string $clientId,

        /**
         * The client secret for the Instagram OAuth application.
         */
        public string $clientSecret,

        /**
         * The redirect URI for the OAuth flow.
         */
        public string|array $redirectUri,

        /**
         * The scopes to request from Instagram.
         *
         * @var string[]
         */
        public array $scopes = ['user_profile', 'user_media'],

        /**
         * Identifier for this OAuth configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createProvider(): AbstractProvider
    {
        return new Instagram([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
        ]);
    }

    public function mapUser(ObjectFactory $factory, ResourceOwnerInterface $resourceOwner): OAuthUser
    {
        return $factory
            ->withData([
                'provider' => 'instagram',
                'raw' => $data = $resourceOwner->toArray(),
                ...$data,
            ])
            ->to(OAuthUser::class);
    }
}
