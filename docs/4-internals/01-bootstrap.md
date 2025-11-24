---
title: Framework bootstrap
description: "Learn the steps involved in bootstrapping the framework."
---

## Overview

Here's a short summary of what booting Tempest looks like.

- The entry point is either `public/index.php` or `./tempest`.
- Tempest boots using the {b`\Tempest\Core\FrameworkKernel`}.
- Bootstrap classes are located in the [`Tempest\Core\Kernel`](https://github.com/tempestphp/tempest-framework/tree/main/packages/core/src/Kernel) namespace.
- First, discovery is started through the {b`\Tempest\Core\LoadDiscoveryLocations`} and {b`\Tempest\Core\LoadDiscoveryClasses`} classes.
- Then, configuration files are registered through the {b`\Tempest\Core\LoadConfig`} class.
- When bootstrapping is completed, the `Tempest\Core\KernelEvent::BOOTED` event is fired.
