---
title: Validation
description: "Tempest's validation is based on built-in PHP types, but provides many attribute-based rules to cover a wide variety of situations."
---

## Overview

Tempest provides a {`\Tempest\Validation\Validator`} object capable of validating an array of values against the public properties of a class or an array of validation rules.

While validation and [data mapping](./01-mapper) often work together, the two are separate components and can also be used separately.

## Validating against objects

When you have raw data and an associated model or data transfer object, you may use the `validateValuesForClass()` method on the {b`\Tempest\Validation\Validator`}. Note that the validator needs to be [resolved from the container](../1-essentials/05-container.md#injecting-dependencies).

```php
$failingRules = $this->validator->validateValuesForClass(Book::class,  [
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
    #[Rules\HasLength(min: 5, max: 50)]
    public string $title;

    #[Rules\IsNotEmptyString]
    public string $description;

    #[Rules\HasDateTimeFormat('Y-m-d')]
    public ?DateTime $publishedAt = null;
}
```

:::info
A list of all available validation rules can be found on [GitHub](https://github.com/tempestphp/tempest-framework/tree/main/packages/validation/src/Rules).
:::

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

If you don't have a model or data transfer object to validate data against, you may alternatively use the `validateValues()` and provide an array of rules.

```php
$this->validator->validateValues([
    'name' => 'Jon Doe',
    'email' => 'jon@doe.co',
    'age' => 25,
], [
    'name' => [new IsString(), new IsNotNull()],
    'email' => [new IsEmail()],
    'age' => [new IsInteger(), new IsNotNull()],
]);
```

If validation fails, `validateValues()` returns a list of fields and their respective failing rules.

:::info
A list of all available validation rules can be found on [GitHub](https://github.com/tempestphp/tempest-framework/tree/main/packages/validation/src/Rules).
:::

## Validating a single value

You may validate a single value against a set of rules using the `validateValue()` method.

```php
$this->validator->validateValue('jon@doe.co', [new IsEmail()]);
```

Alternatively, you may provide a closure for validation. The closure should return `true` if validation passes, or `false` otherwise. You may also return a string to specify the validation failure message.

```php
$this->validator->validateValue('jon@doe.co', function (mixed $value) {
    return str_contains($value, '@');
});
```

## Accessing error messages

When validation fails, a list of fields and their respective failing rules is returned. You may call the `getErrorMessage` method on the validator to get a [localized](./11-localization.md) validation message.

```php
use Tempest\Support\Arr;
use Tempest\Validation\Rules\IsEmail;

// Validate some value
$failures = $this->validator->validateValue('jon@doe.co', new IsEmail());

// Map failures to their message
$errors = Arr\map_iterable($failures, fn (FailingRule $failure) => $this->validator->getErrorMessage($failure));
```

You may also specify the field name of the validation failure to get a localized message for that field.

```php
$this->validator->getErrorMessage($failure, 'email');
// => 'Email must be a valid email address'
```

## Overriding translation messages

You may override the default validation messages by adding a [translation file](../2-features/11-localization.md#defining-translation-messages) anywhere in your codebase. Note that Tempest uses the [MessageFormat 2.0](https://messageformat.unicode.org/) format for localization.

```php app/Localization/validation.en.yml
validation_error:
  is_email: |
    .input {$field :string}
    {$field} must be a valid email address.
```

Sometimes though, you may want to have a specific error message for a rule, without overriding the default translation message for that rule.

This can be done by using the {b`#[Tempest\Validation\TranslationKey]`} attribute on the property being validated. For instance, you may have the following object:

```php
final class Book {
    #[Rules\HasLength(min: 5, max: 50)]
    #[TranslationKey('book_management.book_title')]
    public string $title;
}
```

When this rule fails, the `getErrorMessage()` method from the validator will use `validation_error.has_length.book_management.book_title` as the translation key, instead of `validation_error.has_length`.
