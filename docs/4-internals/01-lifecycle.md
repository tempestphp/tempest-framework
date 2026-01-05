---
title: Framework lifecycle
description: "Learn the steps involved in booting, running and shutting down the framework."
---

## Booting

Tempest's entry point is usually `public/index.php` or `./tempest`. The former uses {b`Tempest\Router\HttpApplication`}, the latter {b`Tempest\Console\ConsoleApplication`}.

When created, the application boots by creating the {b`\Tempest\Core\FrameworkKernel`}:

- it loads the environment, the exception handler, and configures the container,
- it then starts discovery through the {b`\Tempest\Core\LoadDiscoveryLocations`} and {b`\Tempest\Core\LoadDiscoveryClasses`} classes,
- and finally registers configuration files through the {b`\Tempest\Core\LoadConfig`} class.

When bootstrapping is completed, the `Tempest\Core\KernelEvent::BOOTED` event is fired.

## Shutdown

The shutdown process is managed by the kernel's `shutdown()` method, which is called at the end of both HTTP and console lifecycles, as well as in exception handlers. This method:

- runs deferred tasks,
- dispatches the `KernelEvent::SHUTDOWN` event,
- performs any necessary cleanup,
- and terminates the application process.
