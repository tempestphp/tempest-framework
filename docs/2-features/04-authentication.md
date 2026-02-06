---
title: Authentication
description: "Learn how to authenticate models, implement access control, and secure your application with Tempest's flexible authentication system."
keywords: "Experimental"
---

## Overview

Tempest provides an authentication implementation designed to be flexible, not assuming an authenticatable model is a user. This means you can use it for API keys, service accounts, or any other system that requires authentication.

Additionally, Tempest provides a [policy-based access control](#access-control) implementation that allows you to define fine-grained permissions for your resources.

## Quick start

Tempest does not assume that all applications have users, but it is the most common case. For this reason, we provide the ability to publish a basic user model and its migration.

```sh sh
./tempest install auth
```

After publishing, you may run `./tempest migrate`. You now have the building blocks for your authentication.

## Authentication

Tempest's authentication is flexible enough not to assume that an authenticatable model is a user. If your application uses a different system for authentication, such as an API key or a service account, you have the ability to create such a model while preserving the correct nomenclature.

To register an authenticatable model, you may create a class that implements the {b`Tempest\Auth\Authentication\Authenticatable`} interface. This interface is automatically discovered by Tempest.

```php app/Authentication/User.php
use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Hashed;

final class User implements Authenticatable
{
    public PrimaryKey $id;

    public function __construct(
        public string $email,
        #[Hashed]
        #[\SensitiveParameter]
        public ?string $password,
    ) {}
}
```

Note that if you use the default [database authenticatable resolver](#custom-authenticatable-resolver), the model must have at least a {b`Tempest\Database\PrimaryKey`} property—it will be used to uniquely identify the model in the database.

### Authenticating a model

Authenticating a model—in most cases, a user—is usually done in a controller. Tempest provides an {b`Tempest\Auth\Authentication\Authenticator`} that may authenticate, deauthenticate, and access the currently authenticated model.

Because there are a lot of different ways to authenticate users or systems, Tempest doesn't provide the logic to verify authentication credentials. In the case of a user, you may use the {b`Tempest\Cryptography\Password\PasswordHasher`} for this purpose.

```php app/Authentication/AuthenticationController.php
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Cryptography\Password\PasswordHasher;

final readonly class AuthenticationController
{
    public function __construct(
        private Authenticator $authenticator,
        private PasswordHasher $passwordHasher,
    ) {}

    #[Post('/login')]
    public function login(LoginRequest $request): Redirect
    {
        $user = query(User::class)
            ->select()
            ->where('email', $request->email)
            ->first();

        if (! $user || ! $this->passwordHasher->verify($request->password, $user->password)) {
            return new Redirect('/login')->flash('error', 'Invalid credentials');
        }

        $this->authenticator->authenticate($user);

        return new Redirect('/');
    }
    
    #[Post('/logout')]
    public function logout(): Redirect
    {
        $this->authenticator->deauthenticate();
        
        return new Redirect('/login');
    }
}
```

### Accessing the authenticated model

You may access the currently authenticated model by injecting the {b`Tempest\Auth\Authentication\Authenticator`}. The authenticator provides a `current()` method that returns the currently authenticated model, or `null` if no model is authenticated.

```php app/ProfileController.php
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class ProfileController
{
    public function __construct(
        private Authenticator $authenticator,
    ) {}

    #[Get('/profile', middleware: [MustBeAuthenticated::class])]
    public function show(): View
    {
        return view('profile.view.php', user: $this->authenticator->current());
    }
}
```

Alternatively, you may also inject the model directly. For instance, if you have a `User` model implementing `Authenticatable`, it can be injected as a dependency:

```php app/ProfileController.php
final readonly class ProfileController
{
    public function __construct(
        private User $user,
    ) {}

    #[Get('/profile', middleware: [MustBeAuthenticated::class])]
    public function show(): View
    {
        return view('profile.view.php', user: $this->user);
    }
}
```

:::warning
In situations where the model might not be authenticated—for instance, in a route that is not protected by a middleware, you will need to make the property nullable.
:::

### Custom authenticatable resolver

The authenticatable resolver is used internally by the authenticator to resolve an unique identifier from a model and the other way around. Typically, applications use a database to store users, but you can implement custom resolvers to fetch users from other sources, such as LDAP or external APIs.

Tempest provides a {b`Tempest\Auth\Authentication\DatabaseAuthenticatableResolver`}, which is used by default. However, you may implement your own resolver by implementing the {b`Tempest\Auth\Authentication\AuthenticatableResolver`} interface.

```php app/Authentication/LdapAuthenticatableResolver.php
use Tempest\Auth\Authentication\AuthenticatableResolver;
use Tempest\Auth\Authentication\Authenticatable;
use App\Authentication\User;

final readonly class LdapAuthenticatableResolver implements AuthenticatableResolver
{
    public function __construct(
        private LdapClient $ldap,
    ) {}

    public function resolve(int|string $id, string $class): ?Authenticatable
    {
        $attributes = $this->ldap->findUserByIdentifier($id);

        if ($attributes === null) {
            return null;
        }

        return new User(
            username: $attributes['uid'] ?? null,
            email: $attributes['mail'] ?? null,
            displayName: $attributes['cn'] ?? null
        );
    }

    public function resolveId(Authenticatable $authenticatable): int|string
    {
        return $authenticatable->email;
    }
}
```

To instruct Tempest that you want to use your own resolver, you will need to create a dedicated [initializer](../1-essentials/05-container.md#implementing-an-initializer).

```php app/Authentication/LdapAuthenticatableResolverInitializer.php
use Tempest\Auth\Authentication\AuthenticatableResolver;

final class LdapAuthenticatableResolverInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): AuthenticatableResolver
    {
        return new LdapAuthenticatableResolver(
            ldap: $container->get(LdapClient::class),
        );
    }
}
```

### Custom authenticator

By default, Tempest uses the provided {b`Tempest\Auth\Authentication\SessionAuthenticator`} to remember the authenticated model across requests using browser sessions.

However, you may provide your own authenticator by implementing the {b`Tempest\Auth\Authentication\Authenticator`} interface. For instance, may want the model to be authenticated for the duration of the request only.

```php app/Authentication/RequestAuthenticator.php
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Authentication\Authenticatable;

#[Autowire]
final class RequestAuthenticator implements Authenticator
{
    private ?Authenticatable $current = null;

    public function authenticate(Authenticatable $authenticatable): void
    {
        $this->current = $authenticatable;
    }

    public function deauthenticate(): void
    {
        $this->current = null;
    }

    public function current(): ?Authenticatable
    {
        return $this->current;
    }
}
```

## Access control

In most applications, it is necessary to restrict access to certain resources depending on many factors. For instance, you may want to allow only the author of a post to edit it, or allow only administrators to delete other users.

To solve this problem, Tempest provides the ability to write policies. A policy defines the authorization rules for a specific resource, allowing you to implement complex business logic around who can access that resource.

This paradigm is known as [policy-based access control](https://en.wikipedia.org/wiki/Attribute-based_access_control). Policies build on the concept of actions, resources and subjects:

- An action is a specific operation that can be performed on a resource, such as `view`, `edit`, or `delete`.
- A resource may be anything represented by a class.
- A subject is the entity that is trying to perform the action, typically the authenticated user.

### Defining policies

To create a policy, you may define a method in any class and annotate it with the {b`#[Tempest\Auth\AccessControl\Policy]`} attribute. Typically, this is done in a dedicated policy class.

The attribute expects the class name of the resource as its first parameter, and the action name as the second parameter. If the resource is not specified, it will be inferred by the method's first parameter. Similarly, if the action name is not provided, the kebab-cased method name is used instead.

```php app/PostPolicy.php
use Tempest\Auth\AccessControl\Policy;
use Tempest\Auth\AccessControl\AccessDecision;

final class PostPolicy
{
    #[Policy(Post::class)]
    public function create(): bool
    {
        return true;
    }

    #[Policy]
    public function view(Post $post): bool
    {
        if (! $post->published) {
            return false;
        }

        return true;
    }

    #[Policy(action: ['edit', 'update'])]
    public function edit(Post $post, ?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $post->authorId === $user->id->value;
    }
}
```

The policy method will be given the resource instance as the first parameter and the subject as the second one. Both of these may be `null`, depending on the context in which the policy is evaluated.

The policy method is expected to return a boolean value or an {b`Tempest\Auth\AccessControl\AccessDecision`} instance. The latter can be used to provide more context about the decision:

```php
return AccessDecision::denied('You must be authenticated to perform this action.');
```

### Checking for permissions

You may inject the {b`Tempest\Auth\AccessControl\AccessControl`} interface to check if a specific action is granted for a resource and subject. Typically, the `ensureGranted()` method is called in a controller.

```php app/Controllers/PostController.php
use Tempest\Auth\AccessControl\AccessControl;

final readonly class PostController
{
    public function __construct(
        private AccessControl $accessControl,
    ) {}

    #[Delete('/posts/{post}')]
    public function delete(Post $post): Redirect
    {
        $this->accessControl->ensureGranted('delete', $post);

        // Proceed with deletion...
        
        return new Redirect('/posts');
    }
}
```

Alternatively, you may use the `isGranted()` method. It will return an {b`Tempest\Auth\AccessControl\AccessDecision`} instance. Check the `granted` property to determine access for the resource and subject.

:::info
Note that the subject is optional in both methods—if omitted, the [authenticated model](#authentication) is automatically provided.
:::

### Resources without instances

When evaluating the ability to perform an action on a resource without an instance, you may pass the class name of the resource as a string. Typically, this is used when checking if a subject has the permissions to create a new resource.

```php
$accessControl->isGranted('create', resource: Post::class, subject: $user);
```
