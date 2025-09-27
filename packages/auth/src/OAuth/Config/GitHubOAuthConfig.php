<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\Config;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Tempest\Auth\OAuth\OAuthConfig;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Mapper\ObjectFactory;
use UnitEnum;

final class GitHubOAuthConfig implements OAuthConfig
{
    public string $provider = Github::class;

    public function __construct(
        /**
         * The client ID for the GitHub OAuth application.
         */
        public string $clientId,

        /**
         * The client secret for the GitHub OAuth application.
         */
        public string $clientSecret,

        /**
         * The controller action to redirect to after the user authorizes the application.
         */
        public string|array $redirectTo,

        /**
         * The scopes to request from GitHub.
         *
         * @var string[]
         */
        public array $scopes = ['user:email'],

        /**
         * Identifier for this OAuth configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createProvider(): AbstractProvider
    {
        return new Github([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
        ]);
    }

    /**
     * @param GithubResourceOwner $resourceOwner
     */
    public function mapUser(ObjectFactory $factory, ResourceOwnerInterface $resourceOwner): OAuthUser
    {
        return $factory->withData([
            'id' => (string) $resourceOwner->getId(),
            'email' => $resourceOwner->getEmail(),
            'name' => $resourceOwner->getName(),
            'nickname' => $resourceOwner->getNickname(),
            'avatar' => $data['avatar_url'] ?? null,
            'provider' => $this->provider,
            'raw' => $resourceOwner->toArray(),
        ])->to(OAuthUser::class);
    }
}
