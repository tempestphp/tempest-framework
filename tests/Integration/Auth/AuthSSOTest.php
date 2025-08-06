<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth;

use Tempest\Auth\OAuth\GithubOAuthProvider;
use Tempest\Auth\OAuth\OAuthManager;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\env;
use function Tempest\get;

/**
 * @internal
 */
final class AuthSSOTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer->configure(
            __DIR__ . '/install',
            new Psr4Namespace('App\\', __DIR__ . '/install/App'),
        );
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    public function test_api_call(): void
    {
        // CLIC sur bouton "Se connecter avec GitHub"
        // /github/auth/redirect
        // /something/routable
        // /github/auth/callback
        //     return new SSOManager(driver: GithubSSOProvider::class)->fetchUserData();
        // OAuth2Data
        // 'token', 'refreshToken', 'expiresIn'
        // OAuth1Data
        // 'token', 'tokenSecret'
        // All providers
        // 'id', 'nickname', 'name', 'email', 'avatar'
        //        return new SSOManager(driver: GithubSSOProvider::class)->fetchUserDataFromToken( AccessToken $accessToken );
    }
}
