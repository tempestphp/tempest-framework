---
title: Testing
description: "Tempest is built with testing in mind. It ships with convenient utilities that make it easy to test application code without boilerplate."
keywords: ["phpunit", "pest"]
---

## Overview

Tempest uses [PHPUnit](https://phpunit.de) for testing and provides an integration through the [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php) test case. This class boots the framework with configuration suitable for testing, and provides access to multiple utilities.

Testing utilities specific to components are documented in their respective chapters. For instance, testing the router is described in the [routing documentation](./01-routing.md#testing).

## Running tests

Any test class that needs to interact with Tempest must extend [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php).

By default, Tempest ships with a `phpunit.xml` file that configures PHPUnit to find test files in the `tests` directory. You may run tests using the following command:

```sh
./vendor/bin/phpunit
```

## Using the database

By default, tests don't interact with the database. You may manually set up the database for testing in test files by using the `setup()` method on the `database` testing utility.

```php tests/ShowAircraftControllerTest.php
final class ShowAircraftControllerTest extends IntegrationTest
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->database->setup();
    }
}
```

:::info
The [`PreCondition`](https://docs.phpunit.de/en/12.5/attributes.html#precondition) attribute instructs PHPUnit to run the associated method after the `setUp()` method. We recommend using it instead of overriding `setUp()` directly.
:::

### Runnig migrations

By default, all migrations are run when setting up the database. However, you may choose to run only specific migrations by using the `migrate()` method instead of `setup()`.

```php tests/ShowAircraftControllerTest.php
final class ShowAircraftControllerTest extends IntegrationTest
{
    #[Test]
    public function shows_aircraft(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateAircraftTable::class,
        );
        
        // …
    }
}
```

### Using a dedicated testing database

To ensure your tests run in isolation and do not affect your main database, you may configure a dedicated test database connection.

To do so, create a `database.testing.config.php` file anywhere—Tempest will [use it](./06-configuration.md#per-environment-configuration) to override the default database settings.

```php tests/database.testing.config.php
use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/testing.sqlite'
);
```

## Spoofing the environment

By default, Tempest provides a `phpunit.xml` that sets the `ENVIRONMENT` variable to `testing`. This is needed so that Tempest can adapt its boot process and load the proper configuration files for the testing environment.

During tests, you may want to test different paths of your application depending on the environment. For instance, you may want to test that certain features are only available in production. To do this, you may override the {b`Tempest\Core\Environment`} singleton:

```php
use Tempest\Core\Environment;

$this->container->singleton(Environment::class, Environment::PRODUCTION);
```

## Changing the location of tests

The `phpunit.xml` file contains a `{html}<testsuite>` element that configures the directory in which PHPUnit looks for test files. This may be changed to follow any rule of your convenience.

For instance, you may colocate test files and their corresponding class by changing the `{html}suffix` attribute in `phpunit.xml` to the following:

```diff phpunit.xml
<testsuites>
	<testsuite name="Tests">
-		<directory suffix="Test.php">./tests</directory>
+		<directory suffix="Test.php">./app</directory>
	</testsuite>
</testsuites>
```

## Discovering test-specific fixtures

Non-test files created in the `tests` directory are automatically discovered by Tempest when running the test suite.

You can override this behavior by providing your own implementation of `discoverTestLocations()`:

```php tests/Aircraft/ShowAircraftControllerTest.php
use Tempest\Core\DiscoveryLocation;
use Tempest\Framework\Testing\IntegrationTest;

final class ShowAircraftControllerTest extends IntegrationTest
{
    protected function discoverTestLocations(): array
    {
        return [
            new DiscoveryLocation('Tests\\Aircraft', __DIR__ . '/Aircraft'),
        ];
    }
}
```

## Using Pest as a test runner

[Pest](https://pestphp.com/) is a test runner built on top of PHPUnit. It provides a functional way of writing tests similar to JavaScript testing frameworks like [Vitest](https://vitest.dev/), and features an elegant console reporter.

Pest is framework-agnostic, so you may use it in place of PHPUnit if that is your preference. The [installation process](https://pestphp.com/docs/installation) consists of removing the dependency on `phpunit/phpunit` in favor of `pestphp/pest`.

```sh
{:hl-type:composer:} remove {:hl-keyword:phpunit/phpunit:}
{:hl-type:composer:} require {:hl-keyword:pestphp/pest:} --dev --with-all-dependencies
```

The next step is to create a `tests/Pest.php` file, which will instruct Pest how to run tests. You may read more about this file in the [dedicated documentation](https://pestphp.com/docs/configuring-tests).

```php tests/Pest.php
pest()
    ->extend(Tests\IntegrationTest::class)
    ->in(__DIR__);
```

You may now run `./vendor/bin/pest` to run your test suite. You might also want to replace the `phpunit` script in `composer.json` by one that uses Pest.
