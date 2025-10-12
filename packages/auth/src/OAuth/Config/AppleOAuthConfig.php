<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Config;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Apple;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Tempest\Auth\OAuth\OAuthConfig;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Mapper\ObjectFactory;
use UnitEnum;

final class AppleOAuthConfig implements OAuthConfig
{
    public string $provider = Apple::class;

    public function __construct(
        /**
         * The client ID for the Apple OAuth application.
         */
        public string $clientId,

        /**
         * The team ID for the Apple OAuth application.
         */
        public string $teamId,

        /**
         * The key ID for the Apple OAuth application.
         */
        public string $keyId,

        /**
         * The private key for the Apple OAuth application.
         */
        public string $keyFile,

        /**
         * The redirect URI for the OAuth flow.
         */
        public string|array $redirectTo,

        /**
         * The scopes to request from Apple.
         *
         * @var string[]
         */
        public array $scopes = ['name', 'email'],

        /**
         * Identifier for this OAuth configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createProvider(): AbstractProvider
    {
        return new Apple([
            'clientId' => $this->clientId,
            'teamId' => $this->teamId,
            'keyFileId' => $this->keyId,
            'keyFilePath' => $this->keyFile,
        ]);
    }

    public function mapUser(ObjectFactory $factory, ResourceOwnerInterface $resourceOwner): OAuthUser
    {
        return $factory
            ->withData([
                'provider' => 'apple',
                'raw' => $data = $resourceOwner->toArray(),
                ...$data,
            ])
            ->to(OAuthUser::class);
    }
}
