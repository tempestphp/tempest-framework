<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Tempest\Container\HasTag;
use Tempest\Mapper\ObjectFactory;

interface OAuthConfig extends HasTag
{
    /**
     * The OAuth provider class name.
     */
    public string $provider {
        get;
    }

    /**
     * The authorization scopes for this OAuth provider.
     *
     * @return string[]
     */
    public array $scopes {
        get;
    }

    /**
     * The client ID for the OAuth provider.
     */
    public string $clientId {
        get;
    }

    /**
     * The controller action to redirect to after the user authorizes the application.
     */
    public string|array $redirectTo {
        get;
    }

    /**
     * Creates the OAuth provider instance.
     */
    public function createProvider(): AbstractProvider;

    /**
     * Maps a resource owner to an OAuthUser instance.
     */
    public function mapUser(ObjectFactory $factory, ResourceOwnerInterface $resourceOwner): OAuthUser;
}
