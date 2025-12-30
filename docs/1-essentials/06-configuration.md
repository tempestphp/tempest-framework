---
title: Configuration
description: "Tempest takes a unique approach at configuration, providing an excellent developer experience due to its inherent support from code editors."
---

## Overview

Within Tempest, configuration is represented by objects. This allows code editors to provide static insights and autocompletion during edition, resulting in an unmatched developer experience.

Even though the framework is designed to use as little configuration as possible, many configuration classes are available. When fine-grained control over a specific part of the framework is needed, the default configuration can be overwritten.

## Configuration files

Files ending with `*.config.php` are recognized by Tempest's [discovery](../1-essentials/05-discovery) as configuration objects, and will be registered as [singletons](./01-container#singletons) in the container.

```php app/postgres.config.php
use Tempest\Database\Config\PostgresConfig;
use function Tempest\env;

return new PostgresConfig(
    host: env('DB_HOST'),
    port: env('DB_PORT'),
    username: env('DB_USERNAME'),
    password: env('DB_PASSWORD'),
    database: env('DB_DATABASE'),
);
```

The configuration object above instructs Tempest to use PostgreSQL as its database, replacing the framework's default database, SQLite.

## Accessing configuration objects

To access a configuration object, you may inject it from the container like any other dependency.

```php
use Tempest\Core\AppConfig;

final readonly class HomeController
{
    public function __construct(
        private AppConfig $config,
    ) {}

    #[Get('/')]
    public function __invoke(): View
    {
        return view('home.view.php', environment: $this->config->environment);
    }
}
```

## Updating configuration objects

To update a property in a configuration object, you may simply assign a new value. Due to the object being a singleton, the modification will be persisted throught the rest of the application's lifecycle.

```php
use Tempest\Support\Random;
use Tempest\Vite\ViteConfig;

$this->viteConfig->nonce = Random\secure_string(length: 40);
```

Alternatively, you may completely override the configuration instance by calling the `config()` method of the container, registering the new object as a singleton.

```php
$this->container->config(new SQLiteConfig(
    path: root_path('database.sqlite'),
));
```

## Creating your own configuration

As your application grows, you may need to create your own configuration objects. Such a use case could be an integration with Slack, where an API token and an application ID would be required.

You may first create a class representing the configuration needed for your feature. It can have default values for its properties, and even methods if needed.

```php app/Slack/SlackConfig.php
final class SlackConfig
{
    public function __construct(
        public string $token,
        public string $baseUrl,
        public string $applicationId,
        public string $userAgent,
    ) {}
}
```

The next step is to register this configuration object in the container. This can be done by creating a `slack.config.php` file, which will be discovered by Tempest and registered as a [singleton](./01-container#singletons).

```php app/Slack/slack.config.php
use function Tempest\env;

return new SlackConfig(
    token: env('SLACK_API_TOKEN'),
    baseUrl: env('SLACK_BASE_URL', default: 'https://slack.com/api'),
    applicationId: env('SLACK_APP_ID'),
    userAgent: env('USER_AGENT'),
);
```

You may now inject the `SlackConfig` class into a service, a controller, an action, or anything that can be resolved by the container.

```php app/Slack/SlackConnector.php
final class SlackConnector extends HttpConnector
{
    public function __construct(
        private readonly SlackConfig $slackConfig,
    ) {
    }

    public function resolveBaseUrl(): string
    {
        return $this->slackConfig->baseUrl;
    }

    // ...
}
```

## Per-environment configuration

Whenever possible, you should have a single configuration file per feature. You may use the `Tempest\env()` function inside that file to reference credentials and environment-specific values.

However, it's sometimes needed to have completely different configurations in development and in production. For instance, you may use S3 for your [storage](../2-features/05-file-storage.md) in production, but use the local filesystem during development.

When this happens, you may create environment-specific configuration files by using the `.<env>.config.php` suffix. For instance, a production-only configuration file could be `storage.prod.config.php`:

```php app/storage.prod.config.php
return new S3StorageConfig(
    bucket: env('S3_BUCKET'),
    region: env('S3_REGION'),
    accessKeyId: env('S3_ACCESS_KEY_ID'),
    secretAccessKey: env('S3_SECRET_ACCESS_KEY'),
);
```

## Disabling the configuration cache

During development, Tempest will discover configuration files every time the framework is booted. In a production environment, configuration files are automatically cached.

You may override this behavior by setting the `{txt}{:hl-property:CONFIG_CACHE:}` environment variable to `true`.

```env .env
{:hl-property:CONFIG_CACHE:}={:hl-keyword:true:}
```
