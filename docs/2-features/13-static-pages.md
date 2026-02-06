---
title: Static pages
description: "When rendering pages with no dynamic component, booting the whole framework is not necessary. Tempest provides a way to generate static pages that can be rendered directly from your web server."
---

## Overview

When a controller action is tagged with {b`#[Tempest\Router\StaticPage]`}, it can be compiled by Tempest as a static HTML page. These pages can then directly be served directly through your web server.

```php app/Marketing/FrontPageController.php
use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class FrontPageController
{
    #[StaticPage]
    #[Get('/')]
    public function frontpage(): View
    {
        return view('./front-page');
    }
}
```

Compiling and cleaning up static pages is done using the `{txt}static:generate` and `{txt}static:clean` commands, respectively. Note that the latter removes all HTML files and empty directories in your `/public` directory.

```sh
{:hl-comment:./tempest:} static:generate
{:hl-comment:./tempest:} static:clean
```

## Data providers

Since most pages require some form of dynamic data, static pages can be assigned a data provider, which will generate multiple pages for one controller action.

Let's take a look at the controller action for this very website:

```php app/Documentation/ChapterController.php
use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use Tempest\View\View;

final readonly class ChapterController
{
    #[StaticPage(ChapterDataProvider::class)]
    #[Get('/{category}/{slug}')]
    public function show(string $category, string $slug, ChapterRepository $chapters): View
    {
        return new ChapterView(
            repository: $chapters,
            current: $chapters->find($category, $slug),
        );
    }
}
```

In this case, the {b`#[Tempest\Router\StaticPage]`} attribute gets a reference to the `ChapterDataProvider`, which implements the {`\Tempest\Router\DataProvider`} interface:

```php app/Documentation/ChapterDataProvider.php
use Tempest\Router\DataProvider;

final readonly class ChapterDataProvider implements DataProvider
{
    public function provide(): Generator
    {
        // …
    }
}
```

A data provider's goal is to generate multiple pages for one controller action. It does so by yielding an array of controller action parameters for every page that needs to be generated. In case of the documentation chapter controller, the action needs a `$category` and `$slug`, as well as the chapter repository.

That repository is injected by the container, so we don't need to worry about it here. What we do need to provide is a category and slug for each page we want to generate.

In other words: we want to generate a page for every documentation chapter. We can use the `ChapterRepository` to get a list of all available chapters. Eventually, our data provider looks like this:

```php app/Documentation/ChapterDataProvider.php
use Tempest\Router\DataProvider;

final readonly class ChapterDataProvider implements DataProvider
{
    public function __construct(
        private ChapterRepository $chapters
    ) {}

    public function provide(): Generator
    {
        foreach ($this->chapters->all() as $chapter) {
            // Yield an array of parameters that should be passed to the controller action,
            yield [
                'category' => $chapter->category,
                'slug' => $chapter->slug,
            ];
        }
    }
}
```

The only thing left to do is to generate the static pages:

```console
<dim>./tempest static:generate</dim>

/framework/01-getting-started <dim>.............</dim> <em>/public/framework/01-getting-started/index.html</em>
/framework/02-the-container <dim>.................</dim> <em>/public/framework/02-the-container/index.html</em>
/framework/03-controllers <dim>.....................</dim> <em>/public/framework/03-controllers/index.html</em>
/framework/04-views <dim>.................................</dim> <em>/public/framework/04-views/index.html</em>
/framework/05-models <dim>...............................</dim> <em>/public/framework/05-models/index.html</em>
<comment>…</comment>
```

## Crawling for dead links

Optionally, you can instruct the static generate to crawl your pages to scan for dead links. This is done by passing the `--crawl` option to the `static:generate` command:

```console
<dim>./tempest static:generate --crawl</dim>
```

By default, the crawler will only check for internal dead links. If you want to check for external links as well, you can pass the `--external` option:

```console
<dim>./tempest static:generate --crawl --external</dim>
```

## Production

Static pages are generated in the `/public` directory, as `index.html` files. Most web servers will automatically serve these static pages for you without any additional setup.

Note that static pages are meant to be generated as part of your deployment script. That means the `{txt}./tempest static:generate` command should be in your deployment pipeline.
