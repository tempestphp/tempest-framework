---
title: Logging
description: "Learn how to use Tempest's logging features to monitor and debug your application."
---

## Overview

Tempest provides a logging implementation built on top of [Monolog](https://github.com/Seldaek/monolog) that follows PSR-3 and the [RFC 5424 specification](https://datatracker.ietf.org/doc/html/rfc5424). This gives you access to eight standard log levels and the ability to send log messages to multiple destinations simultaneously.

The system supports file logging, Slack integration, system logs, and custom channels. You can configure different loggers for different parts of your application using Tempest's [tagged singletons](../1-essentials/05-container.md#tagged-singletons) feature.

## Writing logs

To start logging messages, you may inject the {b`Tempest\Log\Logger`} interface in any class. By default, log messages will be written to a daily rotating log file stored in `.tempest/logs`. This may be customized by providing a different [logging configuration](#configuration).

```php app/Services/UserService.php
use Tempest\Log\Logger;

final readonly class UserService
{
    public function __construct(
        private Logger $logger,
    ) {}
}
```

Tempest supports all eight levels described in the [RFC 5424](https://tools.ietf.org/html/rfc5424) specification. It is possible to configure channels to only log messages at or above a certain level.

```php
$logger->emergency('System is unusable');
$logger->alert('Action required immediately');
$logger->critical('Important, unexpected error');
$logger->error('Runtime error that should be monitored');
$logger->warning('Exceptional occurrence that is not an error');
$logger->notice('Uncommon event');
$logger->info('Miscellaneous event');
$logger->debug('Detailed debug information');
```

### Providing context

All log methods accept an optional context array for additional information. This data is formatted as JSON and included with your log message:

```php
$logger->error('Order processing failed', context: [
    'user_id' => $order->userId,
    'order_id' => $order->id,
    'total_amount' => $order->total,
    'payment_method' => $order->paymentMethod,
    'error_code' => $exception->getCode(),
    'error_message' => $exception->getMessage(),
]);
```

## Configuration

By default, Tempest uses a daily rotating log configuration that creates a new log file each day and retains up to 31 files:

```php config/logging.config.php
use Tempest\Log\Config\DailyLogConfig;
use Tempest;

return new DailyLogConfig(
    path: Tempest\internal_storage_path('logs', 'tempest.log'),
    maxFiles: Tempest\env('LOG_MAX_FILES', default: 31)
);
```

To configure a different logging channel, you may create a `logging.config.php` file anywhere and return one of the [available configuration classes](#available-configurations-and-channels).

### Specifying a minimum log level

Every configuration class and log channel accept a `minimumLogLevel` property, which defines the lowest severity level that will be logged. Messages below this level will be ignored.

```php config/logging.config.php
use Tempest\Log\Config\MultipleChannelsLogConfig;
use Tempest\Log\Channels\DailyLogChannel;
use Tempest\Log\Channels\SlackLogChannel;
use Tempest;

return new MultipleChannelsLogConfig(
    channels: [
        new DailyLogChannel(
            path: Tempest\internal_storage_path('logs', 'tempest.log'),
            maxFiles: Tempest\env('LOG_MAX_FILES', default: 31),
            minimumLogLevel: LogLevel::DEBUG,
        ),
        new SlackLogChannel(
            webhookUrl: Tempest\env('SLACK_LOGGING_WEBHOOK_URL'),
            channelId: '#alerts',
            minimumLogLevel: LogLevel::CRITICAL,
        ),
    ],
    prefix: null,
);
```

### Using multiple loggers

In situations where you would like to log different types of information to different places, you may create multiple tagged configurations to create separate loggers for different purposes.

For instance, you could have a logger dedicated to critical alerts, while each of your application's module have its own logger:

```php src/Monitoring/logging.config.php
use Tempest\Log\Config\SlackLogConfig;
use Modules\Monitoring\Logging;
use Tempest;

return new SlackLogConfig(
    webhookUrl: Tempest\env('SLACK_LOGGING_WEBHOOK_URL'),
    channelId: '#alerts',
    minimumLogLevel: LogLevel::CRITICAL,
    tag: Logging::SLACK,
);
```

```php src/Orders/logging.config.php
use Tempest\Log\Config\DailyLogConfig;
use Modules\Monitoring\Logging;
use Tempest;

return new DailyLogConfig(
    path: Tempest\internal_storage_path('logs', 'orders.log'),
    tag: Logging::ORDERS,
);
```

Using this approach, you can inject the appropriate logger using [tagged singletons](../1-essentials/05-container.md#tagged-singletons). This gives you the flexibility to customize logging behavior in different parts of your application.

```php src/Orders/ProcessOrder.php
use Tempest\Log\Logger;

final readonly class ProcessOrder
{
    public function __construct(
        #[Tag(Logging::ORDERS)]
        private Logger $logger,
    ) {}

    public function __invoke(Order $order): void
    {
        $this->logger->info('Processing new order', ['order' => $order]);
        
        // ...
    }
}
```

### Available configurations and channels

Tempest provides a few log channels that correspond to common logging needs:

- {b`Tempest\Log\Channels\AppendLogChannel`} — append all messages to a single file without rotation,
- {b`Tempest\Log\Channels\DailyLogChannel`} — create a new file each day and remove old files automatically,
- {b`Tempest\Log\Channels\WeeklyLogChannel`} — create a new file each week and remove old files automatically,
- {b`Tempest\Log\Channels\SlackLogChannel`} — send messages to a Slack channel via webhook,
- {b`Tempest\Log\Channels\SysLogChannel`} — write messages to the system log.

As a convenient abstraction, a configuration class for each channel is provided:

- {b`Tempest\Log\Config\SimpleLogConfig`}
- {b`Tempest\Log\Config\DailyLogConfig`}
- {b`Tempest\Log\Config\WeeklyLogConfig`}
- {b`Tempest\Log\Config\SlackLogConfig`}
- {b`Tempest\Log\Config\SysLogConfig`}

These configuration classes also accept a `channels` property, which allows for providing multiple channels for a single logger. Alternatively, you may use the {b`Tempest\Log\Config\MultipleChannelsLogConfig`} configuration class to achieve the same result more explicitly.

## Debugging

Tempest includes several global functions for debugging. Typically, these functions are for quick debugging and should not be committed to production.

- `ll()` — writes values to the debug log without displaying them,
- `lw()` (also `dump()`) — logs values and displays them,
- `ld()` (also `dd()`) — logs values, displays them, and stops execution,
- `le()` — logs values and emits an {b`Tempest\Debug\ItemsDebugged`} event.

### Tailing debug logs

Debug logs are written with console formatting, so they can be tailed with syntax highlighting. You may use `./tempest tail:debug` to monitor the debug log in real time.

:::warning
By default, debug logs are cleared every time the `tail:debug` command is run. If you want to keep previous log entries, you may pass the `--no-clear` flag.
:::

### Configuring the debug log

By default, the debug log is written to `.tempest/debug.log`. This is configurable by creating a `debug.config.php` file that returns a {b`Tempest\Debug\DebugConfig`} with a different `path`:

```php config/debug.config.php
use Tempest\Debug\DebugConfig;
use Tempest;

return new DebugConfig(
    logPath: Tempest\internal_storage_path('logs', 'debug.log')
);
```
