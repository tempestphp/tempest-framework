## 2.0

Tempest comes with automated upgrades for most of our breaking changes, powered by [Rector](https://getrector.com/). If you prefer to manually upgrade your project, you can review the list of all breaking changes further down this document.

**If you don't have Rector installed in your project, please do so:**

1. `composer require rector/rector --dev` to require rector as a dev dependency
2. Run `vendor/bin/rector` to create a default rector config file

Next, update Tempest; it's important to add the `--no-scripts` flag to prevent any errors from being thrown while updating Tempest.

```sh
composer require tempest/framework:^2.0 --no-scripts
```

Then you should add the `tempest2.php` set to your Rector config file:

```php
return RectorConfig::configure()
    // …
    ->withSets([__DIR__ . '/vendor/tempest/framework/packages/upgrade/src/tempest2.php']);
```

Then run `vendor/bin/rector` to update all your project files. After that, run `tempest discovery:clear` and `tempest key:generate`. The `tempest key:generate` command must only be done once per environment.

Finally, carefully review and test your project, and make sure to read through the list of breaking changes below:

### Breaking changes

- **`Tempest\Database\Id` is now called `Tempest\Database\PrimaryKey`** (https://github.com/tempestphp/tempest-framework/pull/1458)
- **The value property of `Tempest\Database\PrimaryKey` has been renamed from `id` to `value`** (https://github.com/tempestphp/tempest-framework/pull/1458)
- **`Tempest\CommandBus\AsyncCommand` is now called `Tempest\CommandBus\Async`** (https://github.com/tempestphp/tempest-framework/pull/1507)
- **You cannot longer declare view components via the `<x-component name="x-my-component">` tag. All files using this syntax must remove the wrapping `<x-component` tag and instead rename the filename to `x-my-component.view.php` (https://github.com/tempestphp/tempest-framework/pull/1439)**
- **Validation rule names were updated** (https://github.com/tempestphp/tempest-framework/pull/1444)
- **The `DatabaseMigration` interface was split into two** (https://github.com/tempestphp/tempest-framework/pull/1513)
- **`\Tempest\uri` and `\Tempest\is_current_uri` are both moved to the `\Tempest\Router` namespace**
- Cookies are now encrypted by default (https://github.com/tempestphp/tempest-framework/pull/1447) and developers must run `tempest key:generate` once per environment
- Changes in view component variable scoping rules might affect view files (https://github.com/tempestphp/tempest-framework/pull/1435)
- The validator now requires the translator, and should always be injected instead of manually created (https://github.com/tempestphp/tempest-framework/pull/1444)

The changes in **bold** are automated when you use Rector.