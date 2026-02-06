---
title: Processes
description: "Learn how to run synchronous and asynchronous processes, capture their output, and test them."
---

## Overview

Tempest provides a testable wrapper around the [Symfony Process component](https://symfony.com/doc/current/components/process.html), inspired by [Laravel's own wrapper](https://laravel.com/docs/12.x/processes). It allows you to run one or multiple processes synchronously or asynchronously, while being testable and convenient to use.

## Synchronous processes

The {`Tempest\Process\ProcessExecutor`} interface is the entrypoint for invoking processes. It provides a `run()` method to run a process synchronously, and a `start()` method to run it asynchronously. You may access the interface by [injecting it as a dependency](../1-essentials/05-container.md) in your classes.

```php app/Composer.php
use Tempest\Process\ProcessExecutor;

final readonly class Composer
{
    public function __construct(
        private ProcessExecutor $executor
    ) {}

    public function update(): void
    {
        $this->executor->run('composer update');
    }
}
```

The `run()` method returns an instance of {b`Tempest\Process\ProcessResult`}, which contains the output of the process, its exit code, and whether it was successful. You can access these properties to handle the result of the process.

```php app/Composer.php
$result = $this->executor->run('composer update');

$result->successful();
$result->failed();
$result->exitCode;
$result->output;
$result->errorOutput;
```

## Asynchronous processes

To run a process asynchronously, you may use the `start()` method instead. This will return an instance of {b`Tempest\Process\InvokedProcess`}, which you can use to monitor the process.

You may send a signal to a running process using the `signal()` method, or stop it using `stop()`. It is also possible to wait for the process using `wait()`, which accepts a callback to capture the live output of the process.

```php app/Composer.php
$this->executor
    ->start('composer update')
    ->wait(function (OutputChannel $channel, string $output) {
        echo $output;
    });
```

## Process pools

It is possible to execute multiple tasks simultaneously using a process pool. To do so, you may call the `pool()` method on the {`Tempest\Process\ProcessExecutor`}. This returns a {b`Tempest\Process\Pool`} instance, which provides convenient methods for managing the processes.

```php
$pool = $this->executor->pool([
    'composer update',
    'bun install',
]);

$pool->start();
$pool->count();
$pool->forEach(fn (InvokedProcess $process) => /** ... */);
$pool->forEachRunning(fn (InvokedProcess $process) => /** ... */);
$pool->signal(SIGINT);
$pool->stop();
```

Alternatively, if you are only interested in the process outputs, you may use the `concurrently()` method and destructure its results:

```php
[$composer, $bun] = $this->executor->concurrently([
    'composer update',
    'bun install',
]);

echo $composer;
echo $bun;
```

## Testing

Tempest provides a process testing utility accessible through the `process` property of the [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php) test case. You may learn more about testing in the [dedicated chapter](../1-essentials/07-testing.md).

### Mocking processes

Testing process invokation results involves calling `mockProcessResult()` with the command you want to mock and an optional result. This will simulate the command being run and return the result you specified.

```php
// Mocks `composer up` calls
$this->process->mockProcessResult('composer up');

// Call application code...
// ...

// Assert against executed processes
$this->process->assertCommandRan('composer up');
$this->process->assertRan(function (PendingProcess $process, ProcessResult $result) {
    // ...
});
```

### Describing asynchronous processes

When dealing with asynchronous processes, you may use the `describe()` method to define the expectations of the process. This allows you to specify the command, the expected output and error output, the exit code, and the amount of times the `running` property should return `true`.

```php
$this->process->mockProcessResults([
    'composer up' => $this->process
        ->describe()
        ->iterations(1)
        ->output('Nothing to install, update or remove'),
    'bun install' => $this->process
        ->describe()
        ->iterations(4)
        ->output('Checked 225 installs across 274 packages (no changes) [144.00ms]'),
]);

$this->process->assertCommandRan('composer up', function (ProcessResult $result) {
    $this->assertSame("Nothing to install, update or remove\n", $result->output);
});
```

In the example above, `composer up` and `bun install` are mocked to return the specified output. They both return `0` as their exit code by default. The `running` property of the process that runs `composer up` will return `true` only once, while the one that runs `bun install` will return `true` four times.

### Allowing process execution

By default, to prevent unintended side effects, Tempest does not actually execute processes during tests. Instead, trying to execute non-mocked processes will throw an exception.

If you prefer to allow process execution, you may change this behavior by calling `allowRunningActualProcesses()` in your test case. This will allow all processes to be executed, and you may still perform assertions on them.

```php
$this->process->allowRunningActualProcesses();

// Call application code...

$this->process->assertCommandRan('composer up');
```
