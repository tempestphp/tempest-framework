<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth\Installer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\OAuth\SupportedOAuthProvider;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class OAuthInstallerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer
            ->configure(
                __DIR__ . '/install',
                new Psr4Namespace('App\\', __DIR__ . '/install/App'),
            )
            ->setRoot(__DIR__ . '/install')
            ->put('.env.example', '')
            ->put('.env', '');
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    #[Test]
    #[DataProvider('oauthProvider')]
    public function install_oauth_provider(
        SupportedOAuthProvider $provider,
        string $expectedConfigPath,
        string $expectedControllerPath,
    ): void {
        $this->console
            ->call('install auth --oauth')
            ->confirm()
            ->deny()
            ->deny()
            ->input($provider->value)
            ->confirm()
            ->confirm()
            ->confirm()
            ->confirm()
            ->confirm()
            ->assertSee('The selected OAuth provider is installed in your project')
            ->assertSuccess();

        $this->installer
            ->assertFileExists($expectedConfigPath)
            ->assertFileContains($expectedConfigPath, $provider::class)
            ->assertFileExists($expectedControllerPath)
            ->assertFileContains($expectedControllerPath, $provider::class)
            ->assertFileContains('.env', "OAUTH_{$provider->name}_CLIENT_ID")
            ->assertFileContains('.env.example', "OAUTH_{$provider->name}_CLIENT_ID");

        $composerPackage = $provider->composerPackage();
        if ($composerPackage !== null) {
            $this->installer->assertCommandExecuted("composer require {$provider->composerPackage()}");
        }
    }

    public static function oauthProvider(): array
    {
        return [
            'apple' => [
                'provider' => SupportedOAuthProvider::APPLE,
                'expectedConfigPath' => 'App/Authentication/OAuth/apple.config.php',
                'expectedControllerPath' => 'App/Authentication/OAuth/AppleController.php',
            ],
            'discord' => [
                'provider' => SupportedOAuthProvider::DISCORD,
                'expectedConfigPath' => 'App/Authentication/OAuth/discord.config.php',
                'expectedControllerPath' => 'App/Authentication/OAuth/DiscordController.php',
            ],
            'facebook' => [
                'provider' => SupportedOAuthProvider::FACEBOOK,
                'expectedConfigPath' => 'App/Authentication/OAuth/facebook.config.php',
                'expectedControllerPath' => 'App/Authentication/OAuth/FacebookController.php',
            ],
            'generic' => [
                'provider' => SupportedOAuthProvider::GENERIC,
                'expectedConfigPath' => 'App/Authentication/OAuth/generic.config.php',
                'expectedControllerPath' => 'App/Authentication/OAuth/GenericController.php',
            ],
            'github' => [
                'provider' => SupportedOAuthProvider::GITHUB,
                'expectedConfigPath' => 'App/Authentication/OAuth/github.config.php',
                'expectedControllerPath' => 'App/Authentication/OAuth/GithubController.php',
            ],
            'google' => [
                'provider' => SupportedOAuthProvider::GOOGLE,
                'expectedConfigPath' => 'App/Authentication/OAuth/google.config.php',
                'expectedControllerPath' => 'App/Authentication/OAuth/GoogleController.php',
            ],
            'instagram' => [
                'provider' => SupportedOAuthProvider::INSTAGRAM,
                'expectedConfigPath' => 'App/Authentication/OAuth/instagram.config.php',
                'expectedControllerPath' => 'App/Authentication/OAuth/InstagramController.php',
            ],
            'linkedin' => [
                'provider' => SupportedOAuthProvider::LINKEDIN,
                'expectedConfigPath' => 'App/Authentication/OAuth/linkedin.config.php',
                'expectedControllerPath' => 'App/Authentication/OAuth/LinkedInController.php',
            ],
            'microsoft' => [
                'provider' => SupportedOAuthProvider::MICROSOFT,
                'expectedConfigPath' => 'App/Authentication/OAuth/microsoft.config.php',
                'expectedControllerPath' => 'App/Authentication/OAuth/MicrosoftController.php',
            ],
            'slack' => [
                'provider' => SupportedOAuthProvider::SLACK,
                'expectedConfigPath' => 'App/Authentication/OAuth/slack.config.php',
                'expectedControllerPath' => 'App/Authentication/OAuth/SlackController.php',
            ],
            'twitch' => [
                'provider' => SupportedOAuthProvider::TWITCH,
                'expectedConfigPath' => 'App/Authentication/OAuth/twitch.config.php',
                'expectedControllerPath' => 'App/Authentication/OAuth/TwitchController.php',
            ],
        ];
    }
}
