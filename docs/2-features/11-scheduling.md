---
title: Scheduling
description: 'Tempest provides a modern and convenient way of scheduling tasks, which can be any class method, even existing console commands.'
---

## Overview

Dealing with repeating, scheduled tasks is as simple as adding the {`#[Tempest\Console\Schedule]`} attribute to any class method. As with console commands, [discovery](../1-essentials/05-discovery.md) takes care of finding these methods and registering them.

## Using the scheduler

To run tasks on your server, a single cron task is required. This task should call the `schedule:run` command, which will evaluate which scheduled task should be run at the current time.

```
0 * * * * user /path/to/{*tempest schedule:run*}
```

## Defining schedules

Any method using the `{php}#[Schedule]` attribute will be run by the scheduler. As with everything Tempest, these methods are discovered automatically.

```php app/ScheduledTasks.php
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;

final readonly class ScheduledTasks
{
    #[Schedule(Every::HOUR)]
    public function updateSlackChannels(): void
    {
        // …
    }
}
```

For most common scheduling use-cases, the {b`Tempest\Console\Scheduler\Every`} enumeration can be used. In case you need more fine-grained control, you can pass in an {b`Tempest\Console\Scheduler\Interval`} object instead:

```php
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Interval;

#[Schedule(new Interval(hours: 2, minutes: 30))]
public function updateSlackChannels(): void
{
    // …
}
```

Note that scheduled task don't have to be console commands, but they can be both. This is handy when you need a task to be run on a schedule, but also want to be able to run it manually.

```php
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Schedule;

#[Schedule(Every::HOUR)]
#[ConsoleCommand('slack:update-channels')]
public function updateSlackChannels(): void
{
    // …
}
```
