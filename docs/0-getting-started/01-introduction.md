---
title: Introduction
description: "Tempest is a framework for PHP development, designed to get out of your way. Its core philosophy is to help you focus on your application code, without being bothered hand-holding the framework."
---

Tempest's goal is to make you more productive when building web and console apps in PHP. It handles all the boilerplate parts of such projects for you, so that you can focus on what matters the most: writing application code.

People using Tempest say it's the sweet spot between the robustness of Symfony and the eloquence of Laravel. It feels lightweight and close to vanilla PHP; and yet powerful and feature-rich. On this page, you'll read what sets Tempest apart as a framework for modern PHP development. If you're already convinced, you can head over to the [installation page](../0-getting-started/02-installation.md) and get started with Tempest.

## Vision

Tempest's vision can be summarized like this: **it's a community-driven, modern PHP framework that gets out of your way and dares to think outside the box**. Let's dissect that vision in depth.

### Community driven

Tempest started out as an educational project, without the intention for it to be something real. People picked up on it, though, and it was only after a strong community had formed that we considered making it something real.

Currently, there are three core members dedicating a lot of their time to Tempest, as well as over [50 additional contributors](https://github.com/tempestphp/tempest-framework). We have an active [Discord server](/discord) with close to 400 members.

Tempest isn't a solo project and never has been. It is a new framework and has a way to go compared to Symfony or Laravel, but there already is significant momentum and will only keep growing.

### Embracing modern PHP

The benefit of starting from scratch like Tempest did is having a clean slate. Tempest embraced modern PHP features from the start, and its goal is to keep doing this in the future by shipping built-in upgraders whenever breaking changes happen (think of it as Laravel Shift, but built into the framework).

Just to name a couple of examples, Tempest uses property hooks:

```php
interface MigratesUp
{
    public string $name {
        get;
    }

    public function up(): QueryStatement;
}
```

Attributes:

```php
final class BookController
{
    #[Get('/books/{book}')]
    public function show(Book $book): Response { /* … */ }
}
```

Proxy objects:

```php
use Tempest\Container\Proxy;

final readonly class BookController
{
    public function __construct(
        #[Proxy] private SlowDependency $slowDependency,
    ) { /* … */ }
}
```

And a lot more.

### Getting out of your way

A core part of Tempest's philosophy is that it wants to "get out of your way" as best as possible. For starters, Tempest is designed to structure your project code however you want, without making any assumptions or forcing conventions on you. You can prefer a classic MVC application, DDD or hexagonal design, microservices, or something else; Tempest works with any project structure out of the box without any configuration.

Behind Tempest's flexibility is one of its most powerful features: [discovery](../internals/discovery). Discovery gives Tempest a great number of insights into your codebase, without any handholding. Discovery handles routing, console commands, view components, event listeners, command handlers, middleware, schedules, migrations, and more.

```php
final class ConsoleCommandDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly ConsoleConfig $consoleConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            if ($consoleCommand = $method->getAttribute(ConsoleCommand::class)) {
                $this->discoveryItems->add($location, [$method, $consoleCommand]);
            }
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$method, $consoleCommand]) {
            $this->consoleConfig->addCommand($method, $consoleCommand);
        }
    }
}
```

Discovery makes Tempest truly understand your codebase so that you don't have to explain the framework how to use it. Of course, discovery is heavily optimized for local development and entirely cached in production, so there's no performance overhead. Even better: discovery isn't just a core framework feature, you're encouraged to write your own project-specific discovery classes wherever they make sense. That's the Tempest way.

:::info
Read the [getting started with discovery](/blog/discovery-explained) guide if you are new to Tempest.
:::

Besides Discovery, Tempest is designed to be extensible. You'll find that any part of the framework can be replaced and hooked into by implementing an interface and plugging it into the container. No fighting the framework, Tempest gets out of your way.

```php
use Tempest\View\ViewRenderer;

$container->singleton(ViewRenderer::class, $myCustomViewRenderer);
```

### Thinking outside the box

Finally, since Tempest originated as an educational project, many Tempest features dare to rethink the things we've gotten used to. For example, [console commands](../1-essentials/04-console-commands), which in Tempest are designed to be very similar to controller actions:

```php
final readonly class BooksCommand
{
    use HasConsole;
    
    public function __construct(
        private BookRepository $repository,
    ) {}
    
    #[ConsoleCommand]
    public function find(?string $initial = null): void
    {
        $book = $this->search(
            'Find your book',
            $this->repository->find(...),
        );
    }

    #[ConsoleCommand(middleware: [CautionMiddleware::class])]
    public function delete(string $title, bool $verbose = false): void 
    { /* … */ }
}
```

Or what about [Tempest's ORM](../1-essentials/03-database), which aims to have truly decoupled models:

```php
use Tempest\Validation\Rules\Length;
use App\Author;

final class Book
{
    #[Length(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \App\Chapter[] */
    public array $chapters = [];
}
```

```php
final class BookRepository
{
    public function findById(int $id): Book
    {
        return query(Book::class)
            ->select()
            ->with('chapters', 'author')
            ->where('id = ?', $id)
            ->first();
    }
}
```

Then there's our view engine, which embraces the most original template engine of all time: HTML;

```html
<x-base :title="$this->seo->title">
    <ul>
        <li :foreach="$this->books as $book">
            {{ $book->title }}

            <span :if="$this->showDate($book)">
                <x-tag>
                    {{ $book->publishedAt }}
                </x-tag>
            </span>
        </li>
    </ul>
</x-base>
```

## Getting started

Are you intrigued? Want to give Tempest a try? Head over to [the next chapter](../0-getting-started/02-installation.md) to learn about how to get started with Tempest.

If you want to become part of our community, you're more than welcome to [join our Discord server](/discord), and to check out [Tempest on GitHub](https://github.com/tempestphp/tempest-framework).

Enjoy!
