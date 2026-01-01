---
title: Localization
description: "Tempest provides convenient utilities for localizing applications, including a translator built on the MessageFormat 2.0 specification."
---

## Overview

Tempest provides a simple {b`Tempest\Intl\Translator`} interface for localizing applications. It allows you to translate messages into different languages and formats them according to the current or specified locale.

The translator implements the [MessageFormat 2.0](https://messageformat.unicode.org/) specification, which provides a flexible syntax for defining translation messages. This specification is [maintained by the Unicode project](https://github.com/unicode-org/message-format-wg) and is widely used in internationalization libraries.

## Translating messages

To translate messages, you may [inject](../1-essentials/05-container.md) the {`Tempest\Intl\Translator`} interface and use its `translate()` method. If the translation message accepts variables, you may pass them as named parameters.

```php
$translator->translate('cart.expire_at', expire_at: $expiration);
// Your cart is valid until 1:30 PM
```

To translate a message in a specific locale, you may use the `translateForLocale()` instead and provide the {b`Tempest\Intl\Locale`} as the first parameter.

```php
$translator->translateForLocale(Locale::FRENCH, 'cart.expire_at', expire_at: $expiration);
// Votre panier expire à 12h30
```

Alternatively, you may use the `translate` or the `translate_for_locale` function in the `Tempest\Intl` namespace.

### Configuring the locale

The current locale is stored in the `currentLocale` property of the {`Tempest\Intl\IntlConfig`} [configuration object](../1-essentials/06-configuration.md). You may configure another default locale by creating a dedicated configuration file:

```php intl.config.php
return new IntlConfig(
    currentLocale: Locale::FRENCH,
    fallbackLocale: Locale::ENGLISH,
);
```

By default, Tempest uses the [`intl.default_locale`](https://www.php.net/manual/en/locale.getdefault.php) ini value for the current locale.

### Changing the locale

You may update the current locale at any time by mutating the {b`Tempest\Intl\IntlConfig`} configuration object. For instance, this could be done in a [middleware](../1-essentials/01-routing.md#route-middleware):

```php
final readonly class SetLocaleMiddleware implements HttpMiddleware
{
    public function __construct(
        private Authenticator $authenticator,
        private IntlConfig $intlConfig,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $this->intlConfig->currentLocale = $this->authenticator
            ->currentUser()
            ->preferredLocale;

        return $next($request);
    }
}
```

## Defining translation messages

Translation messages are usually stored in translation files. Tempest automatically [discovers](../1-essentials/05-discovery.md) YAML and JSON translation files that use the `<name>.<locale>.{yaml,json}` naming format, where `<name>` may be any string, and `<locale>` must be an [ISO 639-1](https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes) language code.

For instance, you may store translation files in a `lang` directory:

```
src/
└── lang/
    ├── messages.fr.yaml
    └── messages.en.yaml
```

Alternatively, you may call the `add()` method on a {`Tempest\Intl\Catalog\Catalog`} instance to add a translation message at runtime.

```php
$catalog->add(Locale::FRENCH, 'order.continue_shopping', 'Continuer vos achats');
```

### Message syntax

Tempest implements the [MessageFormat 2.0](https://messageformat.unicode.org/) specification, which provides a flexible syntax for defining translation messages. The syntax allows for variables, [pluralization](#pluralization), and [custom formatting functions](#custom-formatting-functions).

Since most translation messages are multiline, YAML is the recommended format for defining them. Here is an example of a translation message that uses a [variable](https://messageformat.unicode.org/docs/reference/variables/), a [function](https://messageformat.unicode.org/docs/reference/functions/) and a function [parameter](https://messageformat.unicode.org/docs/reference/functions/#options):

```yaml messages.en.yaml
today:
  Today is {$today :datetime pattern=|yyyy/MM/dd|}
```

:::info
You may learn more about this syntax in the [MessageFormat documentation](https://messageformat.unicode.org/docs/translators/).
:::

### Pluralization

Pluralizing messages may be done using [matchers](https://messageformat.unicode.org/docs/reference/matchers/) and the `number` function. This syntax supports languages that have more than two plural categories. For instance, you may translate this sentence in Polish:

```php messages.pl.yaml
cart:
  items_count:
    .input {$count :number}
    .match $count
      one   {{Masz {$count} przedmiot.}}
      few   {{Masz {$count} przedmioty.}}
      many  {{Masz {$count} przedmiotów.}}
      other {{Masz {$count} przedmiotów.}}
```

For more complex translation messages, you may also use multiple variables in a matcher. In this example, we use a `type` and a `count` variable in the same matcher.

```php messages.pl.yaml
cart:
  items_by_type_count:
    .input {$type :string}
    .input {$count :number}
    .match $type $count
      product one   {{Masz {$count} produkt w koszyku.}}
      product few   {{Masz {$count} produkty w koszyku.}}
      product many  {{Masz {$count} produktów w koszyku.}}
      product *     {{Masz {$count} produktów w koszyku.}}
      service one   {{Masz {$count} usługę w koszyku.}}
      service few   {{Masz {$count} usługi w koszyku.}}
      service many  {{Masz {$count} usług w koszyku.}}
      service *     {{Masz {$count} usług w koszyku.}}
      *       one   {{Masz {$count} element w koszyku.}}
      *       few   {{Masz {$count} elementy w koszyku.}}
      *       many  {{Masz {$count} elementów w koszyku.}}
      *       *     {{Masz {$count} elementów w koszyku.}}
```

### Using markup

Markup may be added to translation messages using a [dedicated syntax](https://messageformat.unicode.org/docs/reference/markup/) defined in the MessageFormat specification. Tempest provides a markup implementation that renders HTML tags and Iconify icons.

```yaml
bold_text: "This is {#strong}bold{/strong}."
ui:
  open_menu: "{#icon-tabler-menu/} Open menu"
```

It is possible to implement your own markup by implementing the {b`Tempest\Intl\MessageFormat\MarkupFormatter`} or {b`Tempest\Intl\MessageFormat\StandaloneMarkupFormatter`} interfaces. Classes implementing these interfaces are automatically discovered by Tempest.

### Custom formatting functions

The [MessageFormat 2.0](https://messageformat.unicode.org/) specification allows for defining custom formatting functions that can be used in translation messages. By default, Tempest provides formatting functions for strings, numbers and dates.

You may define a custom formatting function by implementing the {b`Tempest\Intl\MessageFormat\FormattingFunction`} interface. For instance, the function for formatting dates is implemented as follows:

```php
final class DateTimeFunction implements FormattingFunction
{
    public string $name = 'datetime';

    public function evaluate(mixed $value, array $parameters): FormattedValue
    {
        $datetime = DateTime::parse($value);
        $formatted = $datetime->format(Arr\get_by_key($parameters, 'pattern'));

        return new FormattedValue($value, $formatted);
    }
}
```
