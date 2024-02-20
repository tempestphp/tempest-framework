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