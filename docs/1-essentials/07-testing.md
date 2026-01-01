---
title: Testing
description: "Tempest is built with testing in mind. It ships with convenient utilities that make it easy to test application code without boilerplate."
keywords: ["phpunit", "pest"]
---

## Overview

Tempest uses [PHPUnit](https://phpunit.de) for testing and provides an integration through the [`Tempest\Framework\Testing\IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php) test case. This class boots the framework with configuration suitable for testing, and provides access to multiple utilities.

Testing utilities specific to components are documented in their respective chapters. For instance, testing the router is described in the [routing documentation](./01-routing.md#testing).

## Running tests

Any test class that wants to interact with Tempest should extend from [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php). Next, any test class should end with the suffix `Test`.

Running the test suite is done by running `composer phpunit`.

```sh
composer phpunit
```

## Test-specific discovery locations

Tempest will only discover non-dev namespaces defined in composer.json automatically. That means that `{:hl-keyword:require-dev:}` namespaces aren't discovered automatically. Whenever you need Tempest to discover test-specific locations, you may specify them within the `discoverTestLocations()` method of the provided `IntegrationTest` class.

On top of that, Tempest _will_ look for files in the `tests/Fixtures` directory and discover them by default. You can override this behavior by providing your own implementation of `discoverTestLocations()`, where you can return an array of `DiscoveryLocation` objects (or nothing).

```php tests/HomeControllerTest.php
use Tempest\Core\DiscoveryLocation;
use Tempest\Framework\Testing\IntegrationTest;

final class HomeControllerTest extends IntegrationTest
{
    protected function discoverTestLocations(): array
    {
        return [
            new DiscoveryLocation('Tests\\OtherFixtures', __DIR__ . '/OtherFixtures'),
        ];
    }
}
```

## Using the database

If you want to test code that interacts with the database, your test class can call the `setupDatabase()` method. This method will create and migrate a clean database for you on the fly.

```php
final class TodoControllerTest extends IntegrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setupDatabase();
    }
}
```

Most likely, you'll want to use a test-specific database connection. You can create a `database.config.php` file anywhere within test-specific discovery locations, and Tempest will use that connection instead of the project's default. For example, you can create a file `tests/Fixtures/database.config.php` like so:

```php tests/Fixtures/database.config.php
use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/database-testing.sqlite'
);
```

By default, no tables will be migrated. You can choose to provide a list of migrations that will be run for every test that calls `setupDatabase()`, or you can run specific migrations on a per-test basis.

```php
final class TodoControllerTest extends IntegrationTest
{
    protected function migrateDatabase(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateTodosTable::class,
        );
    }
}
```

```php
final class TodoControllerTest extends IntegrationTest
{
    public function test_create_todo(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateTodosTable::class,
        );
        
        // …
    }
}
```

## Tester utilities

The `IntegrationTest` provides several utilities to make testing easier. You can read the details about each tester utility on the documentation page of its respective component. For example, there's the [http tester](../1-essentials/01-routing.md#testing) that helps you test HTTP requests:

```php
$this->http
    ->get('/account/profile')
    ->assertOk()
    ->assertSee('My Profile');
```

There's the [console tester](../1-essentials/04-console-commands.md#testing):

```php tests/ExportUsersCommandTest.php
$this->console
    ->call(ExportUsersCommand::class)
    ->assertSuccess()
    ->assertSee('12 users exported');

$this->console
    ->call(WipeDatabaseCommand::class)
    ->assertSee('caution')
    ->submit()
    ->assertSuccess();
```

And many, many more.

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
