---
title: Validation
description: "Tempest's validation is based on built-in PHP types, but provides many attribute-based rules to cover a wide variety of situations."
---

## Overview

Tempest provides a {`\Tempest\Validation\Validator`} object capable of validating an array of values against the public properties of a class or an array of validation rules.

While validation and [data mapping](./01-mapper) often work together, the two are separate components and can also be used separately.

## Validating against objects

When you have raw data and an associated model or data transfer object, you may use the `validateValuesForClass` method on the {b`\Tempest\Validation\Validator`}.

```php
use Tempest\Validation\Validator;

$validator = new Validator();
$failingRules = $validator->validateValuesForClass(Book::class,  [
    'title' => 'Timeline Taxi',
    'description' => 'My sci-fi novel',
    'publishedAt' => '2024-10-01',
]);
```

This method accepts a fully-qualified class name as the first argument, and an array of data as the second. The values of the data array will be validated against the public properties of the class.

In this case, validation works by inferring validation rules from the built-in PHP types. In the example above, the `Book` class has the following public properties:

```php
use Tempest\DateTime\DateTime;

final class Book
{
    public string $title;
    public string $description;
    public ?DateTime $publishedAt = null;
}
```

If validation fails, `validateValuesForClass()` returns a list of fields and their respective failed rules.

### Adding more rules

Most of the time, the built-in PHP types will not be enough to fully validate your data. You may then add validation attributes to the model or data transfer object.

```php
use Tempest\Validation\Rules;

final class Book
{
    #[Rules\Length(min: 5, max: 50)]
    public string $title;

    #[Rules\NotEmpty]
    public string $description;

    #[Rules\DateTimeFormat('Y-m-d')]
    public ?DateTime $publishedAt = null;
}
```

A list of all available validation rules can be found on [GitHub](https://github.com/tempestphp/tempest-framework/tree/main/packages/validation/src/Rules).

### Skipping validation

You may have situations where you don't want specific properties on a model to be validated. In this case, you may use the {b`#[Tempest\Validation\SkipValidation]`} attribute to prevent them from being validated.

```php
use Tempest\Validation\SkipValidation;

final class Book
{
    #[SkipValidation]
    public string $title;
}
```

## Validating against specific rules

If you don't have a model or data transfer object to validate data against, you may alternatively use the `validateValues` and provide an array of rules.

```php
$validator->validateValues([
    'name' => 'Jon Doe',
    'email' => 'jon@doe.co',
    'age' => 25,
], [
    'name' => [new IsString(), new NotNull()],
    'email' => [new Email()],
    'age' => [new IsInteger(), new NotNull()],
]);
```

If validation fails, `validateValues()` returns a list of fields and their respective failing rules.

A list of all available validation rules can be found on [GitHub](https://github.com/tempestphp/tempest-framework/tree/main/packages/validation/src/Rules).

## Validating a single value

You may validate a single value against a set of rules using the `validateValue` method.

```php
$validator->validateValue('jon@doe.co', [new Email()]);
```

Alternatively, you may provide a closure for validation. The closure should return `true` if validation passes, or `false` otherwise. You may also return a string to specify the validation failure message.

```php
$validator->validateValue('jon@doe.co', function (mixed $value) {
    return str_contains($value, '@');
});
```

## Accessing error messages

When validation fails, a list of fields and their respective failing rules is returned. You may call the `message` method on any rule to get a validation message.

```php
use Tempest\Support\Arr;

// Validate some value
$failures = $validator->validateValue('jon@doe.co', new Email());

// Map failures to their message
$errors = Arr\map($failures, fn (Rule $failure) => $failure->message());
```

:::info
Note that we expect to improve the way validation messages work in the future. See [this conversation](https://discord.com/channels/1236153076688359495/1294321824498323547/1294321824498323547) on our [Discord server](https://tempestphp.com/discord).
:::
