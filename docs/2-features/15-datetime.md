---
title: 'Date and time'
description: "Tempest provides a complete alternative to the DateTime implementation, with a higher-level API, deeply integrated into the framework."
keywords: ["timezone", "date", "time", "time zone", "carbon"]
---

## Overview

PHP provides multiple date and time implementations. There is [`DateTime`](https://www.php.net/manual/en/class.datetime.php) and [`DateTimeImmutable`](https://www.php.net/manual/en/class.datetimeimmutable.php), based on [`DateTimeInterface`](https://www.php.net/manual/en/class.datetimeinterface.php), as well as [`IntlCalendar`](https://www.php.net/manual/en/class.intlcalendar.php). Unfortunately, those implementation have rough, low-level, awkward APIs, which are not pleasant to work with.

Tempest provides an alternative to [`DateTimeInterface`](https://www.php.net/manual/en/class.datetimeinterface.php), partially based on [`IntlCalendar`](https://www.php.net/manual/en/class.intlcalendar.php). This implementation provides a better API with a more consistent interface. It was initially created by {x:azjezz} for the [PSL](https://github.com/azjezz/psl), and was ported to Tempest so it could be deeply integrated.

:::info
You're not required to use Tempest's DateTime implementation, and may as well use PHP's native datetime, Carbon, or any other. If you rely on third-party libraries like Carbon, you should read about [global casters and serializers](/2.x/features/mapper#registering-casters-and-serializers-globally) as well to ensure model support.  
:::

## Creating date instances

The {`Tempest\DateTime\DateTime`} class provides a `DateTime::parse()` method to create a date from a string, a timestamp, or another datetime instance. This is the most flexible way to create a date instance.

```php
DateTime::parse('2025-09-19 02:00:00');
```

Alternatively, if you know the format of the date string you are working with, you may use the `DateTime::fromPattern()`. It accepts a standard [ICU format](https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax).

Finally, for more specific use cases, the `DateTime::fromString()` method may be used to create a date from a localized date and time string.

### For the current date and time

The recommended approach for getting the current time is by calling the `now()` method on the {`Tempest\Clock\Clock`} interface, [which may be injected as a dependency](#clock-interface) in any class.

However, for convenience, you may also create a {b`Tempest\DateTime\DateTime`} instance for the current time using the `DateTime::now()` method or the `Tempest\now()` function.

```php
$now = DateTime::now();
```

### From a known format pattern

If you know the format of the date string you are working with, you should prefer using the `DateTime::fromPattern()` method. It accepts a standard [ICU format](https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax).

```php
DateTime::fromPattern('2025-09-19 02:00', pattern: 'yyyy-MM-dd HH:mm');
```

## Manipulating dates

The {b`Tempest\DateTime\DateTime`} class provides multiple methods to manipulate dates. You may add or subtract time from a date using the `plus()` and `minus()` methods, which accept a {b`Tempest\DateTime\Duration`} instance.

For convenience, more specific manipulation methods are also provided.

```php
// Adding a set duration
$date->plus(Duration::seconds(30));

// Using convenience methods
$date->plusHour();
$date->plusMinutes(30);
$date->minusDay();
$date->endOfDay();
```

### Converting timezones

All datetime creation methods accept a `timezone` parameter. This parameter accepts an instance of the {b`Tempest\DateTime\Timezone`} enumeration. When not provided, the default timezone, provided by [`date.timezone`](https://www.php.net/manual/en/datetime.configuration.php#ini.date.timezone), will be used.

You may convert the timezone of an existing instance by calling the `convertToTimezone()` method:

```php
use Tempest\DateTime\DateTime;
use Tempest\DateTime\Timezone;

$date = DateTime::now();
$date->convertToTimezone(Timezone::EUROPE_PARIS);
```

### Computing a duration

By calling the `between()` method on a date instance, you may compute the duration between this date and a second one. This method returns a {b`Tempest\DateTime\Duration`} instance.

```php
use Tempest\DateTime\DateTime;

$date1 = DateTime::now();
$date2 = DateTime::parse('2025-09-19 02:00:00');
$duration = $date1->between($date2);
```

### Comparing dates

The {b`Tempest\DateTime\DateTime`} instance provides multiple methods to compare dates against each other, or against the current time. For instance, you may check if a date is before or after another date using the `isBefore()` and `isAfter()` methods, respectively.

```php
// Check if a date is before another date (exclusive - does not include the comparison date)
$date->isBefore($other);

// Check if a date is before or at another date (inclusive - includes the comparison date)
$date->isBeforeOrAt($other);

// Check if a date between two other dates, inclusively
$date->betweenTimeInclusive($otherDate1, $otherDate2);

// Check if a date is in the future
$date->isFuture();
```

## Formatting dates

You may format a {b`Tempest\DateTime\DateTime`} instance in a specific format using the `format()` method. This method accepts an optional format string, which is a standard [ICU format](https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax), and an optional locale.

```php
use Tempest\DateTime\FormatPattern;
use Tempest\Intl\Locale;

$date->format(); // Jan 7, 2026, 10:30:05 PM
$date->format(pattern: FormatPattern::COOKIE); // Wednesday, 07-Jan-2026 22:30:46 UTC
$date->format(locale: Locale::FRENCH); // 7 janv. 2026, 22:32:12
```

## Clock interface

Tempest provides a {`Tempest\Clock\Clock`} interface which may be [injected as a dependency](../1-essentials/05-container.md#injecting-dependencies) in any class. This is the recommended way of working with time.

```php
final readonly class HomeController
{
    public function __construct(
        private readonly Clock $clock,
    ) {}

    public function __invoke(): View
    {
        return view('./home.view.php', currentTime: $this->clock->now());
    }
}
```

Note that because Tempest has its own {b`Tempest\DateTime\DateTime`} implementation, the {b`Tempest\Clock\Clock`} interface is not compatible with PSR-20. However, you may get a PSR-20 implementation by calling the `toPsrClock()` method.

```php
$psrClock = $clock->toPsrClock();
```

## Testing time

Tempest provides a time-related testing utilities accessible through the `clock` method of the [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php) test case.

Calling this method replaces the {b`Tempest\Clock\Clock`} instance in the container with a testing one, on which a specific date and time can be defined. {b`Tempest\DateTime\DateTime`} instances created with the `DateTime::now()` method or `Tempest\now()` function will use the date and time specified by the testing clock.

```php
// Create a testing clock
$clock = $this->clock();

// Set a specific date and time
$clock->setNow('2025-09-19 02:00:00');

// Advance time by the specified duration
$clock->sleep(milliseconds: 250);
```
