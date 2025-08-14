---
title: Authentication and authorization
keywords: "Experimental"
---

:::warning
The authentication and authorization implementations of Tempest are currently experimental. Although you can use them, please note that they are not covered by our backwards compatibility promise.
:::

## Overview

Logging in (authentication) and verifying whether a user is allowed to perform a specific action (authorization) are two crucial parts of any web application. Tempest comes with a built-in authenticator and authorizer, as well as a base `User` and `Permission` model (if you want to).

## Authentication

Logging in a user can be done with the `Authenticator` class:

```php
// app/AuthController.php

use Tempest\Auth\Authenticator;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;

final readonly class AuthController
{
    public function __construct(
        private Authenticator $authenticator
    ) {}

    #[Post('/login')]
    public function login(Request $request): Response
    {
        $user = // …

        $this->authenticator->login($user);

        return new Redirect('/');
    }
}
```

Note that Tempest currently doesn't provide user management support (resolving a user from a request, user registration, password reset flow, etc.).

## Authorization

You can protect controller routes using the `#[Allow]` attribute:

```php
// app/AdminController.php

use Tempest\Auth\Allow;
use Tempest\Http\Response;

final readonly class AdminController
{
    #[Allow('permission')]
    public function index(): Response
    {
        // …
    }
}
```

Tempest uses a permission-based authorizer. That means that, in order for users to be allowed access to a route, they'll need to be granted the right permission. Permissions can be represented as strings or enums:

```php
// app/AdminController.php

use Tempest\Auth\Allow;
use Tempest\Http\Response;

final readonly class AdminController
{
    #[Allow(UserPermission::ADMIN)]
    public function index(): Response
    {
        // …
    }
}
```

## Built-in user model

Tempest's authenticator and authorizer are compatible with any class implementing the {`Tempest\Auth\CanAuthenticate`} and {`Tempest\Auth\CanAuthorize`} interfaces. However, Tempest comes with a pre-built `User` model that makes it easier to get started. In order to use Tempest's `User` implementation, you must install the auth files:

```
./tempest install auth
./tempest migrate:up
```

With this `User` model, you already have a lot of helper methods in place to build your own user management flow:

```php
use App\Auth\User;

$user = new User(
    name: 'Brent',
    email: 'brendt@stitcher.io',
)
    ->setPassword('password')
    ->save()
    ->grantPermission('admin');
```
