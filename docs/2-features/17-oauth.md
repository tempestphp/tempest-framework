---
title: OAuth
description: "Learn how to implement OAuth to authenticate users with many different providers, such as GitHub, Google, Discord, and many others."
keywords: "Experimental"
---

## Overview

Tempest provides the ability to authenticate users with many OAuth providers, such as GitHub, Google, Discord, and many others, using the same interface.

This implementation is built on top of the PHP league's [OAuth client](https://github.com/thephpleague/oauth2-client)—a reliable, battle-tested OAuth 2.0 client library.

## Getting started

Tempest provides an installer to quickly set up OAuth in your project. You can run the installer using the following command:

```sh
./tempest install auth --oauth
```

The installer will:
- Prompt you to select one or more OAuth providers from the available options
- Publish the necessary configuration files and controller stubs
- Optionally add the OAuth credentials to your `.env` and `.env.example` files
- Optionally install the required Composer dependencies for the selected providers

This is the quickest way to get started with OAuth in your Tempest application.

Alternatively, you can manually create a configuration file for your desired OAuth provider.

Tempest provides a [different configuration object for each provider](#available-providers). For instance, if you wish to authenticate users with GitHub, you may create a `github.config.php` file returning an instance of {b`Tempest\Auth\OAuth\Config\GitHubOAuthConfig`}:

```php app/Auth/github.config.php
return new GitHubOAuthConfig(
    clientId: env('GITHUB_CLIENT_ID'),
    clientSecret: env('GITHUB_CLIENT_SECRET'),
    redirectTo: [GitHubOAuthController::class, 'callback'],
    scopes: ['user:email'],
);
```

In this example, the GitHub OAuth credentials are specified in the `.env`, so different credentials can be configured depending on the environment.

Once your OAuth provider is configured, you may interact with it by using the {`Tempest\Auth\OAuth\OAuthClient`} interface. This is usually done through [dependency injection](../1-essentials/05-container.md#injecting-dependencies).

## Implementing the OAuth flow

To implement a complete OAuth flow for your application, you will need two routes.

- The first one will redirect the user to the OAuth provider's authorization page,
- The second one, which will be redirected to once the user authorizes your application, will fetch the user's information thanks to the code provided by the OAuth provider.

The {b`Tempest\Auth\OAuth\OAuthClient`} interface has the necessary methods to handle both parts of the flow. The following is an example of a complete OAuth flow, including CSRF protection, creating or updating the user, and authenticating them against the application:

```php app/Auth/DiscordOAuthController.php
use Tempest\Auth\OAuth\OAuthClient;

final readonly class DiscordOAuthController
{
    public function __construct(
        private OAuthClient $oauth,
        private Session $session,
        private Authenticator $authenticator,
    ) {}

    #[Get('/auth/discord')]
    public function redirect(): Redirect
    {
        return $this->oauth->createRedirect(scopes: ['identify']);
    }

    #[Get('/auth/discord/callback')]
    public function callback(Request $request): Redirect
    {
        $user = $this->oauth->authenticate(
            request: $request,
            map: fn (OAuthUser $user): User => query(User::class)->updateOrCreate([
                'discord_id' => $user->id,
            ], [
                'discord_id' => $user->id,
                'username' => $user->nickname,
                'email' => $user->email,
            ])
        );

        return new Redirect('/');
    }
}
```

Of course, this example assumes that the database and an [authenticatable model](../2-features/04-authentication.md#authentication) are configured.

### Working with the OAuth user

When an OAuth flow is completed and you call `fetchUser`, you will receive an {b`Tempest\Auth\OAuth\OAuthUser`} object containing the user's information from the OAuth provider:

```php
$user = $this->oauth->fetchUser($code);

$user->id;         // The unique identifier for the user from the OAuth provider
$user->email;      // The user's email address
$user->name;       // The user's name
$user->nickname;   // The user's nickname/username
$user->avatar;     // The user's avatar URL
$user->provider;   // The OAuth provider name
$user->raw;        // Raw user data from the OAuth provider
```

As seen in the example above, you can use this information to create or update a user in your database, or to authenticate them directly.

## Configuring a provider

Most providers require only a `clientId`, `clientSecret` and `redirectTo`, but some might need other parameters. A typical configuration looks like the following:

```php app/Auth/github.config.php
return new GitHubOAuthConfig(
    clientId: env('GITHUB_CLIENT_ID'),
    clientSecret: env('GITHUB_CLIENT_SECRET'),
    redirectTo: [GitHubOAuthController::class, 'callback'],
    scopes: ['user:email'],
);
```

Note that the `redirectTo` accepts a tuple of a controller class and a method name, which will be resolved to the full URL of the route handled by that method. You may also provide an URI path if you prefer.

### Supporting multiple providers

If you need to work with multiple OAuth providers, you may create multiple OAuth configurations using tags. These tags may then be used to resolve the {b`Tempest\Auth\OAuth\OAuthClient`} interface, which will use the corresponding configuration.

It's a good practice to use an enum for the tag:

```php app/Auth/Provider.php
enum Provider
{
    case GITHUB;
    case GOOGLE;
    case DISCORD;
}
```

```php app/Auth/github.config.php
return new GitHubOAuthConfig(
    tag: Provider::GITHUB,
    clientId: env('GITHUB_CLIENT_ID'),
    clientSecret: env('GITHUB_CLIENT_SECRET'),
    redirectTo: [OAuthController::class, 'handleGitHubCallback'],
    scopes: ['user:email'],
);
```

```php app/Auth/google.config.php
return new GoogleOAuthConfig(
    tag: Provider::GOOGLE,
    clientId: env('GOOGLE_CLIENT_ID'),
    clientSecret: env('GOOGLE_CLIENT_SECRET'),
    redirectTo: [GoogleOAuthController::class, 'handleGoogleCallback'],
);
```

Once you have configured your OAuth providers and your tags, you may inject the {b`Tempest\Auth\OAuth\OAuthClient`} interface using the corresponding tag:

```php app/AuthController.php
use Tempest\Container\Tag;

final readonly class AuthController
{
    public function __construct(
        #[Tag(OAuthProvider::GITHUB)]
        private OAuthClient $githubClient,
        #[Tag(OAuthProvider::GOOGLE)]
        private OAuthClient $googleClient,
    ) {}

    #[Get('/auth/github')]
    public function redirectToGitHub(): Redirect
    {
        // ...

        return new Redirect($this->githubClient->getAuthorizationUrl());
    }

    #[Get('/auth/github/callback')]
    public function handleGitHubCallback(Request $request): Redirect
    {
        $githubUser = $this->githubClient->handleCallback($request->get('code'));

        // ...
    }

    // Do the same for Google
}
```

### Using a generic provider

If you need to implement OAuth with a provider that Tempest doesn't have a specific configuration for, you may use the {b`Tempest\Auth\OAuth\Config\GenericOAuthConfig`}:

```php app/Auth/custom.config.php
return new GenericOAuthConfig(
    clientId: env('CUSTOM_CLIENT_ID'),
    clientSecret: env('CUSTOM_CLIENT_SECRET'),
    redirectTo: [OAuthController::class, 'handleCallback'],
    urlAuthorize: 'https://provider.com/oauth/authorize',
    urlAccessToken: 'https://provider.com/oauth/token',
    urlResourceOwnerDetails: 'https://provider.com/api/user',
    scopes: ['read:user'],
);
```

### Available providers

Tempest provides a different configuration object for each OAuth provider. Below are the ones that are currently supported:

- **GitHub** authentication using {b`Tempest\Auth\OAuth\Config\GitHubOAuthConfig`},
- **Google** authentication using {b`Tempest\Auth\OAuth\Config\GoogleOAuthConfig`},
- **Facebook** authentication using {b`Tempest\Auth\OAuth\Config\FacebookOAuthConfig`},
- **Discord** authentication using {b`Tempest\Auth\OAuth\Config\DiscordOAuthConfig`},
- **Instagram** authentication using {b`Tempest\Auth\OAuth\Config\InstagramOAuthConfig`},
- **LinkedIn** authentication using {b`Tempest\Auth\OAuth\Config\LinkedInOAuthConfig`},
- **Microsoft** authentication using {b`Tempest\Auth\OAuth\Config\MicrosoftOAuthConfig`},
- **Slack** authentication using {b`Tempest\Auth\OAuth\Config\SlackOAuthConfig`},
- **Apple** authentication using {b`Tempest\Auth\OAuth\Config\AppleOAuthConfig`},
- **Twitch** authentication using {b`Tempest\Auth\OAuth\Config\TwitchOAuthConfig`},
- Any other OAuth platform using {b`Tempest\Auth\OAuth\Config\GenericOAuthConfig`}.

## Testing

By extending {`Tempest\Framework\Testing\IntegrationTest`} from your test case, you gain access to the OAuth testing utilities through the `oauth` property.

These utilities include a way to replace the OAuth client with a testing implementation, as well as a few assertion methods related to OAuth flows.

### Faking an OAuth client

You may generate a fake, testing-only OAuth client by calling the `fake()` method on the `oauth` property. This will replace the OAuth client implementation in the container, and provide useful assertion methods.

```php tests/AuthControllerTest.php
$oauth = $this->oauth->fake(new OAuthUser(
    id: 'jon',
    email: 'jondoe@example.test',
    nickname: 'jondoe',
));
```

Below is an example of a complete testing flow for an OAuth authentication:

```php tests/AuthControllerTest.php
final class OAuthControllerTest extends IntegrationTestCase
{
    #[Test]
    public function oauth(): void
    {
        // We create a fake OAuth client that will return
        // the specified user when the OAuth flow is completed
        $oauth = $this->oauth->fake(new OAuthUser(
            id: 'jon',
            email: 'jondoe@example.test',
            nickname: 'jondoe',
        ));

        // We first simulate a call to the endpoint
        // that redirects to the provider
        $this->http
            ->get('/oauth/discord')
            ->assertRedirect($oauth->lastAuthorizationUrl);

        // We check that the authorization URL was generated,
        // optionally specifying scopes and options
        $oauth->assertAuthorizationUrlGenerated();

        // We then simulate the callback from the provider
        // with a fake code and the expected state
        $this->http
            ->get("/oauth/discord/callback", query: ['code' => 'some-fake-code', 'state' => $oauth->getState()])
            ->assertRedirect('/');

        // We assert that an access token was retrieved
        // with the same fake code we provided before
        $oauth->assertUserFetched(code: 'some-fake-code');

        // Finally, we ensure a user was created with the
        // credentials we specified in the fake OAuth user
        $user = query(User::class)
            ->find(discord_id: 'jon')
            ->first();

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('jondoe@example.test', $user->email);
        $this->assertSame('jondoe', $user->username);
    }
}
```
