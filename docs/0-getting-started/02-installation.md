---
title: Installation
description: Tempest can be installed as a standalone PHP project, as well as a package within existing projects. The framework modules can also be installed individually, including in projects built on other frameworks.
---

## Prerequisites

Tempest requires PHP [8.5+](https://www.php.net/downloads.php) and [Composer](https://getcomposer.org/) to be installed. Optionally, you may install either [Bun](https://bun.sh) or [Node](https://nodejs.org) if you chose to bundle front-end assets.

For a better experience, it is recommended to have a complete development environment, such as [ServBay](https://www.servbay.com), [Herd](https://herd.laravel.com/docs), or [Valet](https://laravel.com/docs/valet). However, Tempest can serve applications using PHP's built-in server just fine.

Once the prerequisites are installed, you can chose your installation method. Tempest can be a [standalone application](#creating-a-tempest-application), or be added [in an existing project](#tempest-as-a-package)—even one built on top of another framework.

## Creating a Tempest application

To get started with a new Tempest project, you may use {`tempest/app`} as the starting point. The `composer create-project` command will scaffold it for you:

```sh
{:hl-keyword:composer:} create-project tempest/app {:hl-type:my-app:}
{:hl-keyword:cd:} {:hl-type:my-app:}
```

If you have a dedicated development environment, you may then access your application by opening `{txt}https://my-app.test` in your browser. Otherwise, you may use PHP's built-in server:

```sh
{:hl-keyword:php:} tempest serve
{:hl-comment:PHP 8.5.1 Development Server (http://localhost:8000) started:}
```

### Scaffolding front-end assets

Optionally, you may install a basic front-end scaffolding that includes [Vite](https://vite.dev/) and [Tailwind CSS](https://tailwindcss.com/). To do so, run the Vite installer and follow through the wizard:

```sh
{:hl-keyword:php:} tempest install vite --tailwind
```

The assets created by this wizard, `main.entrypoint.ts` and `main.entrypoint.css`, are automatically discovered by Tempest. You can serve them using the [`<x-vite-tags />`](../1-essentials/03-views#x-vite-tags) component in your templates.

You may then [run the front-end development server](../2-features/02-asset-bundling.md#running-the-development-server), which will serve your assets on-the-fly:

```bash
{:hl-keyword:npm:} run dev
```

## Tempest as a package

If you already have a project, you can opt to install {`tempest/framework`} as a standalone package. You could do this in any project; it could already contain code, or it could be an empty project.

```sh
{:hl-keyword:composer:} require tempest/framework
```

Installing Tempest this way will give you access to the Tempest console, `./vendor/bin/tempest`. Optionally, you can choose to install Tempest's entry points in your project. To do so, you may run the framework installer:

```txt
{:hl-keyword:./vendor/bin/tempest:} install framework
```

This installer will prompt you to install the following files into your project:

- `public/index.php` — the web application entry point
- `tempest` – the console application entry point
- `.env.example` – a clean example of a `.env` file
- `.env` – the real environment file for your local installation

You can choose which files you want to install, and you can always rerun the `install` command at a later point in time.

## Project structure

Tempest won't impose any file structure on you: one of its core features is that it will scan all project and package code for you, and will automatically discover any files the framework needs to know about.

For instance, Tempest is able to differentiate between a controller method and a console command by looking at the code, instead of relying on naming conventions or configuration files.

:::info
This concept is called [discovery](../1-essentials/05-discovery), and is one of Tempest's most powerful features.
:::

The following project structures work the same way in Tempest, without requiring any specific configuration:

```txt
.                                    .
└── src                              └── src
    ├── Authors                          ├── Controllers
    │   ├── Author.php                   │   ├── AuthorController.php
    │   ├── AuthorController.php         │   └── BookController.php
    │   └── authors.view.php             ├── Models
    ├── Books                            │   ├── Author.php
    │   ├── Book.php                     │   ├── Book.php
    │   ├── BookController.php           │   └── Chapter.php
    │   ├── Chapter.php                  ├── Services
    │   └── books.view.php               │   └── PublisherGateway.php
    ├── Publishers                       └── Views
    │   └── PublisherGateway.php             ├── authors.view.php
    └── Support                              ├── books.view.php
        └── x-base.view.php                  └── x-base.view.php
```

## About discovery

Discovery works by scanning your project code and looking at each file and method individually to determine what that code does. In production environments, [Tempest caches the discovery process](../1-essentials/05-discovery#discovery-in-production), avoiding any performance overhead.

As an example, Tempest is able to determine which methods are controller methods based on their [route attributes](../1-essentials/01-routing.md), or to detect console commands based on methods annotated with {b`#[Tempest\Console\ConsoleCommand]`}:

:::code-group

```php app/BlogPostController.php
use Tempest\Router\Get;
use Tempest\Http\Response;
use Tempest\View\View;

final readonly class BlogPostController
{
    #[Get('/blog')]
    public function index(): View
    { /* … */ }

    #[Get('/blog/{post}')]
    public function show(Post $post): Response
    { /* … */ }
}
```

```php app/RssSyncCommand.php
use Tempest\Console\HasConsole;
use Tempest\Console\ConsoleCommand;

final readonly class RssSyncCommand
{
    #[ConsoleCommand('rss:sync')]
    public function __invoke(bool $force = false): void
    {
        // …
    }
}
```

:::

:::tip{tabler:link}
Learn more about discovery in the [dedicated documentation](../1-essentials/05-discovery.md).
:::
