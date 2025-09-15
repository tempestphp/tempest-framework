## Upgrade guide


1. Make sure rector is installed:
   - `composer require rector/rector --dev`
   - Run `vendor/bin/rector` if you don't have a `rector.php` config file
2. Add the necessary rector sets in your `rector.php` config file:

```php
return RectorConfig::configure()
    // …
    ->withSets([__DIR__ . '/vendor/tempest/framework/packages/upgrade/src/tempest2.php']);
```

3. Run `vendor/bin/rector`