---
title: Primitive utilities
description: "Working with strings and arrays in PHP is notoriously hard due to the lack of a standard library. Tempest comes with a bunch of utilities to improve the experience in this area."
---

## Overview

Tempest provides a set of utilities that make working with primitive values easier. It provides an object-oriented API for handling strings and arrays, along with many namespaced functions to work with arithmetic operations, regular expressions, random values, pluralization, filesystem paths and more.

## Namespaced functions

Most utilities provided by Tempest have a function-based implementation under the [`Tempest\Support`](https://github.com/tempestphp/tempest-framework/tree/main/packages/support/src) namespace. You may look at what is available on GitHub:

- [Regular expressions](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Regex/functions.php)
- [Arithmetic operations](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Math/functions.php)
- [Filesystem operations](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Filesystem/functions.php)
- [Filesystem paths](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Path/functions.php)
- [Json manipulation](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Json/functions.php)
- [Random values](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Random/functions.php)
- [Pluralization](https://github.com/tempestphp/tempest-intl)
- [PHP namespaces](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Namespace/functions.php)

Tempest also provides the {`Tempest\Support\IsEnumHelper`} trait to work with enumerations, since a functional API is not useful in this case.

## String utilities

Tempest provides string utilities through [namespaced functions](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Str/functions.php) or a fluent, object-oriented API, which comes in an immutable and a mutable flavor.

Providing a string value, you may create an instance of {`\Tempest\Support\Str\ImmutableString`} or {`\Tempest\Support\Str\MutableString`}:

```php
use Tempest\Support\Str;
use Tempest\Support\Str\ImmutableString;

// Functional API
$title = Str\to_sentence_case($title);

// Object-oriented API
$slug = new ImmutableString('/blog/01-chasing-bugs-down-the-rabbit-hole/')
    ->stripEnd('/')
    ->afterLast('/')
    ->replaceRegex('/\d+-/', '')
    ->slug()
    ->toString();
```

Note that you may use the `str()` function as a shorthand to create an {b`\Tempest\Support\Str\ImmutableString`} instance.

## Array utilities

Tempest provides array utilities through [namespaced functions](https://github.com/tempestphp/tempest-framework/blob/main/packages/support/src/Arr/functions.php) or a fluent, object-oriented API, which comes in an immutable and a mutable flavor.

Providing an iterable value, you may create an instance of {`\Tempest\Support\Arr\ImmutableArray`} or {`\Tempest\Support\Arr\MutableArray`}:

```php
use Tempest\Support\Arr;
use Tempest\Support\Arr\ImmutableArray;

// Functional API
$first = Arr\first($collection);

// Object-oriented API
$items = new ImmutableArray(glob(__DIR__ . '/content/*.md'))
    ->reverse()
    ->map(function (string $path) {
        // …
    })
    ->mapTo(BlogPost::class);
```

Note that you may use the `arr()` function as a shorthand to create an {b`\Tempest\Support\Arr\ImmutableArray`} instance.

## Recommendations

We recommend working with primitive utilities when possible instead of using PHP's built-in methods. For instance, you may read a file by using `Filesystem\read_file`:

```php
use Tempest\Support\Filesystem;

$contents = Filesystem\read_file(__DIR__ . '/content.md');
```

Using this function covers more edge cases and throws clear exceptions that are easier to catch. Similarly, it may not be useful to always reach for the object-oriented array and string helpers. Sometimes, you may simply use a single function:

```php
use Tempest\Support\Str;
use function Tempest\Support\str;

{- $title = str('My title')->title()->toString(); -}
{+ $title = Str\to_title_case('My title'); +}
```
