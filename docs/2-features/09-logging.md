---
title: Logging
---

Logging is an essential part of any developer's job. Whether it's for debugging or for production monitoring. Tempest has a powerful set of tools to help you access the relevant information you need.

## Debug log

First up are Tempest's debug functions: `ld()` (log, die), `lw()` (log, write), and `ll()` (log, log). These three functions are similar to Symfony's var dumper and Laravel's `dd()`, although there's an important difference.

You can think of `ld()` or `lw()` as Laravel's `dd()` and `dump()` variants. In fact, Tempest uses Symfony's var-dumper under the hood, just like Laravel. Furthermore, if you haven't installed Tempest in a project that already includes Laravel, Tempest will also provide `dd()` and `dump()` as aliases to `ld()` and `lw()`.

The main difference is that Tempest's debug functions will **also write to the debug log**, which can be tailed with tempest's built-in `tail` command. This is its default output:

```console
./tempest tail

<h2>Project</h2> Listening at /Users/brent/Dev/tempest-docs/log/tempest.log
<h2>Server</h2> <error>No server log configured in LogConfig</error>
<h2>Debug</h2> Listening at /Users/brent/Dev/tempest-docs/log/debug.log
```

Wherever you call `ld()` or `lw()` from, the output will also be written to the debug log, and tailed automatically with the `./tempest tail` command. On top of that, `tail` also monitors two other logs:

- The **project log**, which contains everything the default logger writes to
- The **server log**, which should be manually configured in `LogConfig`:

```php
// app/Config/log.config.php

use Tempest\Log\LogConfig;

return new LogConfig(
    serverLogPath: '/path/to/nginx.log'

    // …
);
```

If you're only interested in tailing one or more specific logs, you can filter the `tail` output like so:

```console
./tempest tail --debug

<h2>Debug</h2> Listening at /Users/brent/Dev/tempest-docs/log/debug.log
```

Finally, the `ll()` function will do exactly the same as `lw()`, but **only write to the debug log, and not output anything in the browser or terminal**.

## Logging channels

On top of debug logging, Tempest includes a monolog implementation which allows you to log to one or more channels. Writing to the logger is as simple as injecting `\Tempest\Log\Logger` wherever you'd like:

```php
// app/Rss.php

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Log\Logger;

final readonly class Rss
{
    public function __construct(
        private Console $console,
        private Logger $logger,
    ) {}

    #[ConsoleCommand]
    public function sync()
    {
        $this->logger->info('Starting RSS sync');

        // …
    }
}
```

If you're familiar with [monolog](https://seldaek.github.io/monolog/), you know how it supports multiple handlers to handle a log message. Tempest adds a small layer on top of these handlers called channels, they can be configured within `LogConfig`:

```php
// app/Config/log.config.php

use Tempest\Log\LogConfig;
use Tempest\Log\Channels\AppendLogChannel;

return new LogConfig(
    channels: [
        new AppendLogChannel(path: __DIR__ . '/../log/project.log'),
    ]
);
```

**Please note:**

- Currently, Tempest only supports the `AppendLogChannel` and `DailyLogChannel`, but we're adding more channels in the future. You can always add your own channels by implementing `\Tempest\Log\LogChannel`.
- Also, it's currently not possible to configure environment-specific logging channels, this we'll also support in the future. Again, you're free to make your own channels that take the current environment into account.
