---
title: Testing
description: "Tempest is built with testing in mind. It ships with convenient utilities that make it easy to test application code without boilerplate."
keywords: ["phpunit", "pest"]
---

## Overview

Tempest uses [PHPUnit](https://phpunit.de) for testing and provides an integration through the [`Tempest\Framework\Testing\IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php) test case. This class boots the framework with configuration suitable for testing, and provides access to multiple utilities.

Testing utilities specific to components are documented in their respective chapters. For instance, testing the router is described in the [routing documentation](./01-routing.md#testing).

## Running tests

If you created a Tempest application through the [recommended installation process](../0-getting-started/02-installation.md), you already have access to `tests/IntegrationTestCase`, which your application tests can inherit from.

In this case, you may use the `composer phpunit` command to run your test suite.

```sh
composer phpunit
```

## Creating new test files

By default, PHPUnit is configured to look for test files that end in `*Test.php` in the root `tests` directory. You may create a such a file and make it extend `IntegrationTestCase`.

```php tests/HomeControllerTest.php
use Tests\IntegrationTestCase;

final class HomeControllerTest extends IntegrationTestCase
{
    public function test_index(): void
    {
        $this->http
            ->get('/')
            ->assertOk();
    }
}
```

## Test-specific discovery locations

Tempest does not discover files outside of the namespaces defined in the `require` object of `composer.json`. If you need Tempest to discover test-specific fixture files, you may specify paths using the `discoveryLocations` property of the provided `IntegrationTestCase` class.

For instance, you may create a `tests/config` directory that contains test-specific configuration files, and instruct Tempest to discover them:

```php tests/IntegrationTestCase.php
use Tempest\Core\DiscoveryLocation;

final class IntegrationTestCase extends TestCase
{
    protected string $root = __DIR__ . '/../';

    protected function setUp(): void
    {
        $this->discoveryLocations = [
            new DiscoveryLocation(namespace: 'Tests\\Config', path: __DIR__ . '/config'),
        ];

        parent::setUp();
    }
}
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
    ->extend(Tests\IntegrationTestCase::class)
    ->in(__DIR__);
```

You may now run `./vendor/bin/pest` to run your test suite. You might also want to replace the `phpunit` script in `composer.json` by one that uses Pest.
