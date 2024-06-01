# Welcome to Tempest contributing guide! 🌊🌊🌊

We welcome your PRs and contributions. If you have any feature requests or bug reports, head over to the [issue page](https://github.com/tempestphp/tempest-framework/issues) and feel free to create an issue.

If you'd like to send PRs, you can check out and run Tempest locally like so:

```php
git clone git@github.com:tempestphp/tempest-framework.git
cd tempest-framework/
composer update
```

Before submitting PRs, run `composer qa` locally:

```php
composer qa
```

Please see below for some general guidelines relating to specific components of the framework.

## Acronym Casing
Tempest uses a modified version of the [.NET best practices](https://learn.microsoft.com/en-us/previous-versions/dotnet/netframework-4.0/ms229043(v=vs.100)?redirectedfrom=MSDN) for acronym casing. Please see below for our guidelines:

__Do capitalize all characters of two to three character acronyms, except the first word of a camel-cased identifier.__
A class named `IPAddress` is an example of a short acronym (IP) used as the first word of a Pascal-cased identifier. A parameter named `ipAddress` is an example of a short acronym (ip) used as the first word of a camel-cased identifier.

__Do capitalize only the first character of acronyms with four or more characters, except the first word of a camel-cased identifier.__
A class named `UuidGenerator` is an example of a long acronym (Uuid) used as the first word of a Pascal-cased identifier. A parameter named `uuidGenerator` is an example of a long acronym (uuid) used as the first word of a camel-cased identifier.

__Do not capitalize any of the characters of any acronyms, whatever their length, at the beginning of a camel-cased identifier.__
A class named `Uuid` is an example of a long acronym (Uuid) used as the first word of a camel-cased identifier. A parameter named `dbUsername` is an example of a short acronym (db) used as the first word of a camel-cased identifier.

## Validation Rules
Validation rules should be `final` and `readonly`. The message returned by a validation rule should not include ending
punctuation.

### Best Practices
1. __Use of `final` and `readonly`__: Ensure that validation rules are declared as final and readonly whenever possible. This practice promotes immutability and prevents inadvertent changes to the validation logic.
2. __Error Message Formatting__:
    - __Avoid Ending Punctuation__: When crafting error messages for validation rules, refrain from including ending punctuation such as periods, exclamation marks, or question marks. This helps in maintaining a uniform style and prevents inconsistency in error message presentation.

__:white_check_mark: Good Example__
> Value should be a valid email address

__:x: Bad Example__
> Value should be a valid email address!