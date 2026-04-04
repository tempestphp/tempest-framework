---
title: Class Variance
description: "The Class Variance package can be used standalone or with Tempest framework to provide a tailwind-variants style CSS variant system."
---
## Introduction

Class variance — a PHP package for building component CSS class strings with variants, compound variants, slot support, and conflict-aware merging. Heavily inspired by [CVA](https://cva.style) and [Tailwind-Variants](https://www.tailwind-variants.org/), but with the ability to extend and support other frameworks also.

Two flavours:

- **`cv()`** — separator-based merging, framework-agnostic CSS, somewhat limited by default but can be extended to support other frameworks.
- **`tv()`** — builds on `cv()` with Tailwind-aware merging, aiming to provide the same outcomes as [tailwind-merge](https://github.com/dcastil/tailwind-merge).

## Installation

### With Tempest

```bash
composer require tempest/class-variance
```

Discovery handles the rest. The `CvMergerInitializer` and `TvMergerInitializer` are Discovered automatically, binding tagged `ClassMerger` singletons for injection.

To customise the configuration, publish a config file:

```bash
./tempest class-variance:publish:config
```

This scaffolds a `class-variance.config.php` in your app's source directory that Tempest discovers automatically. You can, as with other packages, move this wherever you wish, as Discovery will handle that for you.

### Standalone (without Tempest)

```bash
composer require tempest/class-variance
```

Import the helper functions and call them directly — no container required:

```php
use function Tempest\ClassVariance\cv;
use function Tempest\ClassVariance\tv;
```


## `cv()` — Generic class variance

`cv()` uses a separator heuristic to resolve conflicts: everything before the first `-` is treated as the group key, and the last class in a group wins.

```php
use function Tempest\ClassVariance\cv;

$button = cv(
    base: 'btn',
    variants: [
        'size' => [
            'sm' => 'btn-sm',
            'md' => 'btn-md',
            'lg' => 'btn-lg',
        ],
        'color' => [
            'primary' => 'btn-primary',
            'danger'  => 'btn-danger',
        ],
    ],
    defaultVariants: [
        'size' => 'md',
    ],
);

echo $button();                               // 'btn btn-md'
echo $button(['color' => 'primary']);         // 'btn btn-md btn-primary'
echo $button(['size' => 'lg', 'color' => 'danger']); // 'btn btn-lg btn-danger'
```

### Compound variants

Apply extra classes only when multiple props match simultaneously:

```php
$badge = cv(
    base: 'badge',
    variants: [
        'color'   => ['primary' => 'badge-primary', 'danger' => 'badge-danger'],
        'outline' => ['true' => 'badge-outline'],
    ],
    compoundVariants: [
        [
            'color'   => 'danger',
            'outline' => 'true',
            'class'   => 'border-danger',
        ],
    ],
    defaultVariants: ['color' => 'primary'],
);

echo $badge(['outline' => 'true']);                         // 'badge badge-primary badge-outline'
echo $badge(['color' => 'danger', 'outline' => 'true']);   // 'badge badge-danger badge-outline border-danger'
```

### Slots

Split the output across named slots — useful for multi-element components:

```php
$card = cv(
    base: [
        'base'   => 'card',
        'header' => 'card-header',
        'body'   => 'card-body',
    ],
    variants: [
        'size' => [
            'sm' => ['base' => 'card-sm', 'body' => 'p-2'],
            'lg' => ['base' => 'card-lg', 'body' => 'p-8'],
        ],
    ],
    defaultVariants: ['size' => 'sm'],
);

echo $card(slot: 'base');                    // 'card card-sm'
echo $card(slot: 'header');                  // 'card-header'
echo $card(['size' => 'lg'], slot: 'body');  // 'card-body p-8'
```

:::info
When your base defines exactly one slot, the slot is inferred automatically, and will internally default to 'base' — you do not need to pass `slot:`.
:::

### Passing extra classes

Pass `class` or `className` in the props to append caller-supplied classes (these go through the same merger):

```php
echo $button(['color' => 'primary', 'class' => 'w-full']); // 'btn btn-md btn-primary w-full'
```

:::info
Generally speaking, standardise on using `class` - `className` is provided only as a syntax courtesy for users of other, older CVA tools which are using this, however consider best practice to use `class` going forward.
:::


## `tv()` — Tailwind-aware class variance

`tv()` ships with the full Tailwind CSS class group definitions from `tailwind-merge`. Conflicting utilities are deduplicated automatically — `p-2 p-4` resolves to `p-4`, `bg-red-500 bg-blue-500` resolves to `bg-blue-500`. The package implements a 'right-wins' approach which ensures that custom classes always override the default.

The API is identical to `cv()`:

```php
use function Tempest\ClassVariance\tv;

$button = tv(
    base: 'inline-flex items-center rounded px-3 py-1.5 text-sm font-medium',
    variants: [
        'intent' => [
            'primary'   => 'bg-blue-600 text-white hover:bg-blue-700',
            'secondary' => 'bg-gray-100 text-gray-900 hover:bg-gray-200',
            'danger'    => 'bg-red-600 text-white hover:bg-red-700',
        ],
        'size' => [
            'sm' => 'px-2 py-1 text-xs',
            'lg' => 'px-4 py-2 text-base',
        ],
    ],
    defaultVariants: ['intent' => 'primary'],
);

echo $button();                          // base + primary intent classes
echo $button(['intent' => 'danger']);    // px-3 wins over default, bg-red-600 replaces bg-blue-600
echo $button(['size' => 'sm']);          // px-2 py-1 text-xs override base padding and text size
```

The conflict resolution means a caller can safely pass overriding classes without knowing what the component already applies:

```php
// Caller overrides just the background — no class duplication
echo $button(['intent' => 'primary', 'class' => 'bg-indigo-600']);
// → '...text-white hover:bg-blue-700 bg-indigo-600'  (bg-blue-600 removed)
```

## Customising the Tailwind config

### Publish the config file (Tempest)

```bash
./tempest class-variance:publish:config
```

The published file returns a `TailwindClassVarianceConfig` object that Tempest discovers automatically. We don't currently publish a GenericConfig file, but you can use the same stub to create your own.

### Single callsite

Pass `config:` directly to override for one specific call. Everything else in your app uses the default.

```php
$config = new TailwindClassVarianceConfig(prefix: 'tw-', extend: new Classmap(/* ... */));

$button = tv(base: 'tw-rounded tw-px-3', config: $config);
```

### Whole component class

Inject the tagged ClassMerger singleton. It picks up your app's config automatically.

```php
use Tempest\ClassVariance\ClassMerger;
use Tempest\Container\Tag;

final readonly class ButtonComponent
{
    public function __construct(
        #[Tag('tv')] private ClassMerger $merger, // or Tag('cv')
    ) {}

    public function render(string $intent = 'primary'): string
    {
        $button = tv(
            base: 'rounded px-3 py-1.5',
            variants: ['intent' => ['primary' => 'bg-blue-600', 'danger' => 'bg-red-600']],
        );

        return $button(['intent' => $intent]);
    }
}

final readonly class ButtonComponent
{
    public function __construct(
        #[Tag('cv')] private ClassMerger $merger,
    ) {}
}
```

### Override the default for all usages

Publish a config file and Tempest will discover it automatically. It is registered in the container and used by both the tagged ClassMerger singletons and any tv() / cv() function calls throughout your app — no config: argument needed anywhere.

```php class-variance.config.php
use Tempest\ClassVariance\Classmaps\Classmap;
use Tempest\ClassVariance\Config\TailwindClassVarianceConfig;

return new TailwindClassVarianceConfig(
    prefix: 'tw-',
    extend: new Classmap(
        classGroups: [
            'scrollbar' => [['scrollbar' => ['hide', 'default']]],
        ],
    ),
);
```

```php class-variance.config.php
use Tempest\ClassVariance\Config\GenericClassVarianceConfig;

return new GenericClassVarianceConfig(separator: '__');
```

For standalone use (no Tempest container), pass `config:` at each callsite or assign it once via a shared variable in your bootstrap.

## Implementing a custom CSS kit

If you're using a CSS framework other than Tailwind you can provide your own conflict-resolution strategy by implementing `ClassVarianceConfig`.

### 1. Implement `ClassMerger`

```php
use Tempest\ClassVariance\ClassMerger;

final readonly class DaisyUiMerger implements ClassMerger
{
    public function merge(string ...$classes): string
    {
        // Last class per group wins. Group = everything before the first '-'.
        $resolved = [];

        foreach ($classes as $class) {
            $group = str_contains($class, '-')
                ? substr($class, 0, strpos($class, '-'))
                : $class;

            $resolved[$group] = $class;
        }

        return implode(' ', array_values($resolved));
    }
}
```

### 2. Implement `ClassVarianceConfig`

```php
use Tempest\ClassVariance\ClassMerger;
use Tempest\ClassVariance\Config\ClassVarianceConfig;

final class DaisyUiConfig implements ClassVarianceConfig
{
    public ClassMerger $merger {
        get => new DaisyUiMerger();
    }
}
```

### 3. Pass your config to `cv()` or `tv()`

```php
$button = cv(
    base: 'btn',
    variants: [
        'color' => ['primary' => 'btn-primary', 'ghost' => 'btn-ghost'],
        'size'  => ['sm' => 'btn-sm', 'lg' => 'btn-lg'],
    ],
    config: new DaisyUiConfig(),
);
```

### 4. Bind in Tempest (optional)

Return your config object from a `*.config.php` file — Tempest will discover it automatically and use it for any `cv()` or `tv()` calls that don't receive an explicit `$config` argument:

```php daisy-ui.config.php
return new DaisyUiConfig();
```

Or bind it explicitly in an initializer if you need constructor arguments:

```php
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class DaisyUiConfigInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): DaisyUiConfig
    {
        return new DaisyUiConfig();
    }
}
```

:::info
If you do create a binding for a CSS UI kit, please consider [contributing](https://github.com/tempestphp/tempest-framework/pulls) it back!
:::