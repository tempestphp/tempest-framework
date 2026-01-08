---
title: Container
description: "Learn how Tempest's container works, how to inject and resolve dependencies, and how to implement initialization logic for your service classes when they need it."
---

## Overview

A dependency container is a system that manages the creation and resolution of objects within an application. Instead of manually instantiating dependencies, classes declare what they need, and the container provides them automatically.

Tempest has a dependency container capable of resolving dependencies without any configuration. Most features are built upon this concept, from controllers to console commands, through event handlers and the command bus.

## Injecting dependencies

The constructors of classes resolved by the container may be any class or interface associated with a [dependency initializer](#dependency-initializers). Similarly, invoked methods such as [event handlers](../2-features/08-events.md), [console commands](../3-console/02-building-console-commands) and invokable classes may also be called directly from the container.

```php app/Aircraft/AircraftService.php
use App\Aircraft\ExternalAircraftProvider;
use App\Aircraft\AircraftRepository;
use Tempest\Console\ConsoleCommand;

final readonly class AircraftService
{
    public function __construct(
        private ExternalAircraftProvider $externalAircraftProvider,
        private AircraftRepository $repository,
    ) {}

    #[ConsoleCommand]
    public function synchronize(): void
    {
        // …
    }
}
```

### Invoking a method or function

If you have access to the container instance, you may call its `{php}invoke()` method to call another method, function or invokable class, resolving its dependencies along the way.

Using named arguments, it is also possible to manually specify parameters on the invoked method:

```php
$this->container->invoke(TrackOperatingAircraft::class, type: AircraftType::PC12);
```

The `{php}\Tempest\invoke()` function serves the same purpose when the container is not directly accessible.

### Locating a dependency

There are situations where it may not be possible to inject a dependency on a constructor. To work around this, Tempest provides the `{php}\Tempest\get()` function, which can resolve an object from the container.

```php
use function Tempest\get;

$config = get(AppConfig::class);
```

:::warning
Resolving services this way should only be used as a last resort. If you are interested in knowing why, you may read more about service location in this [blog post](https://stitcher.io/blog/service-locator-anti-pattern).
:::

## Dependency initializers

When you need fine-grained control over how a dependency is constructed instead of relying on Tempest's autowiring capabilities, you can use initializer classes.

Initializers are classes that know how to construct a specific class or interface. Whenever that class or interface is requested from the container, Tempest will use its corresponding initializer to construct it.

### Implementing an initializer

Initializers are classes that implement the {`Tempest\Container\Initializer`} interface. The `initialize()` method receives the container as its only parameter, and returns an instantiated object.

**Most importantly**, Tempest knows which object this initializer is tied to thanks to the return type of the `initialize()` method, which needs to be typed.

```php app/MarkdownInitializer.php
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class MarkdownInitializer implements Initializer
{
    public function initialize(Container $container): MarkdownConverter
    {
        $environment = new Environment();
        $highlighter = new Highlighter(new CssTheme());

        $highlighter
            ->addLanguage(new TempestViewLanguage())
            ->addLanguage(new TempestConsoleWebLanguage())
            ->addLanguage(new ExtendedJsonLanguage());

        $environment
            ->addExtension(new CommonMarkCoreExtension())
            ->addExtension(new FrontMatterExtension())
            ->addRenderer(FencedCode::class, new CodeBlockRenderer($highlighter))
            ->addRenderer(Code::class, new InlineCodeBlockRenderer($highlighter));

        return new MarkdownConverter($environment);
    }
}
```

The above example is an initializer for a `MarkdownConverter` class. It will set up a markdown converter, configure its extensions, and finally return the object. Whenever `MarkdownConverter` is requested via the container, this initializer class will be used to construct it.

### Matching multiple classes or interfaces

The container may match several classes to a single initializer if it has a union return type.

```php app/MarkdownInitializer.php
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class MarkdownInitializer implements Initializer
{
    public function initialize(Container $container): MarkdownConverter|Markdown
    {
        // …
    }
}
```

### Dynamically matching classes or interfaces

While initializers are capable of resolving almost all situations, there are times where the return type of `initialize` is not enough and more flexibility is needed.

Let's take use the concept of route model binding as an example. A controller might accept an instance of a model as its parameters:

```php app/BookController.php
use Tempest\Router\Get;
use Tempest\Http\Response;

final readonly class BookController
{
    #[Get('/books/{book}')]
    public function show(Book $book): Response { /* … */ }
}
```

Since `$book` isn't a scalar value, Tempest will try to resolve `{php}Book` from the container whenever this controller action is invoked. This means we need an initializer that's able to match the `Book` model:

```php app/BookInitializer.php
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final class BookInitializer implements Initializer
{
    public function initialize(Container $container): Book
    {
        // …
    }
}
```

While this approach works, it would be very inconvenient to create an initializer for every model class. Furthermore, we want route binding to be provided by the framework, so we need a more generic approach.

The {`Tempest\Container\DynamicInitializer`} interface provides a `canInitialize` method, in which the logic for matching a class may be implemented:

```php app/RouteBindingInitializer.php
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final class RouteBindingInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        return $class->getType()->matches(Model::class);
    }

    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): object
    {
        // …
    }
}
```

## Autowired dependencies

When you need to assign a default implementation to an interface without any specific instantiation steps, creating an initializer class for a single line of code might feel excessive.

```php app/AircraftServiceInitializer.php
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class AircraftServiceInitializer implements Initializer
{
    public function initialize(Container $container): AircraftServiceInterface
    {
        return new AircraftService();
    }
}
```

For simple one-to-one mappings, you can skip the initializer class, instead using the `#[Autowire]` attribute on the default implementation. Tempest will discover this, and link that class to the interface it implements:

```php app/AircraftService.php
use Tempest\Container\Autowire;

#[Autowire]
final readonly class AircraftService implements AircraftServiceInterface
{
    // …
}
```

## Singletons

If you need to register a class as a singleton in the container, you can use the `#[Singleton]` attribute. Any class can have this attribute:

```php app/Services/AircraftService/Client.php
use Tempest\Container\Singleton;
use Tempest\HttpClient\HttpClient;

#[Singleton]
final readonly class Client
{
    public function __construct(
        private HttpClient $http,
    ) {}

    public function fetch(Icao $icao): Aircraft
    {
        // …
    }
}
```

Furthermore, an initializer method can be annotated as a `#[Singleton]`, meaning its return object will only ever be resolved once:

```php app/MarkdownInitializer.php
use Tempest\Console\ConsoleCommand;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class MarkdownInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): MarkdownConverter|Markdown
    {
        // …
    }
}
```

### Tagged singletons

In some cases, you want more control over singleton definitions.

Let's say you want an instance of `{php}\Tempest\Highlight\Highlighter` that would be configured for web highlighting, and one that would be configured CLI highlighting. In this situation, you can differentiate them using the `tag` parameter of the `#[Singleton]` attribute:

```php app/WebHighlighterInitializer.php
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class WebHighlighterInitializer implements Initializer
{
    #[Singleton(tag: 'web')]
    public function initialize(Container $container): Highlighter
    {
        return new Highlighter(new CssTheme());
    }
}
```

Retrieving this specific instance from the container may be done by using the `{php}#[Tag]` attribute during autowiring:

```php app/HttpExceptionHandler.php
use Tempest\Container\Tag;

class HttpExceptionHandler implements ExceptionHandler
{
    public function __construct(
        #[Tag('web')]
        private Highlighter $highlighter,
    ) {}
}
```

If you have a container instance, you may also get it directly using the `tag` argument:

```php
$container->get(Highlighter::class, tag: 'cli');
```

:::info
[This blog post](https://stitcher.io/blog/tagged-singletons), by {gh:brendt}, provides in-depth explanations about tagged singletons.
:::

### Dynamic tags

Some components implement the {`Tempest\Container\HasTag`} interface, which requires a `tag` property. Singletons using this interface are tagged by the `tag` property, essentially providing the ability to have dynamic tags.

This is specifically useful to get multiple instances of the same configuration. This is how [multiple database connections support](../1-essentials/03-database.md#using-multiple-connections) is implemented.

## Built-in types dependencies

Besides being able to depend on objects, sometimes you'd want to depend on built-in types like `string`, `int` or more often `array`. It is possible to depend on these built-in types, but these cannot be autowired and must be initialized through a [tagged singleton](#tagged-singletons).

For example if we want to group a specific set of validators together as a tagged collection, you can initialize them in a tagged singleton initializer like so:

```php
// app/BookValidatorsInitializer.php

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class BookValidatorsInitializer implements Initializer
{
    #[Singleton(tag: 'book-validators')]
    public function initialize(Container $container): array
    {
        return [
            $container->get(HeaderValidator::class),
            $container->get(BodyValidator::class),
            $container->get(FooterValidator::class),
        ];
    }
}
```

Now you can use this group of validators as a normal tagged value in your container:

```php
// app/BookController.php

use Tempest\Container\Tag;

final readonly class BookController
{
    public function __construct(
        #[Tag('book-validators')] private readonly array $contentValidators,
    ) { /* … */ }
}
```

## Injected properties

While constructor injection is almost always the preferred way to go, Tempest also offers the ability to inject values straight into properties, without them being requested by the constructor.

You may mark any property—public, protected, or private—with the `#[Inject]` attribute. Whenever a class instance is resolved via the container, its properties marked for injection will be provided the right value. Tagged singletons may also be injected using the optional `tag` parameter of the `#[Inject]` attribute.

```php Tempest/Console/src/HasConsole.php
use Tempest\Container\Inject;

trait HasConsole
{
    #[Inject]
    private Console $console;

    // …
}
```

Keep in mind that injected properties are a form of service location. While it's recommended to rely on constructor injection by default, injected properties may offer flexibility when using traits without having to claim the constructor within that trait.

For example, without injected properties, the above example would have to define a constructor within the trait to inject the `Console` dependency:

```php
trait HasConsole
{
    public function __construct(
        private readonly Console $console,
    ) {}

    // …
}
```

On its own, that isn't a problem, but it causes some usability issues when using this trait in classes that require other dependencies as well:

```php
use Tempest\Console\HasConsole;

class MyCommand
{
    use HasConsole;

    public function __construct(
        private BlogPostRepository $repository,

        // The `HasConsole` trait breaks if you didn't remember to explicitly inject it here
        private Console $console,
    ) {}

    // …
}
```

For these edge cases, it's nicer to make the trait self-contained without having to rely on constructor injection. That's why injected properties are supported.

## Decorators

The container supports the [decorator pattern](https://refactoring.guru/design-patterns/decorator), which allows you to wrap objects and add new behavior to them at runtime without changing their structure. This is particularly useful for adding cross-cutting concerns like logging, caching, validation, or authentication.

To create a decorator, you need to:

1. Use the {b`#[Tempest\Container\Decorates]`} attribute on your decorator class
2. Implement the same interface as the class you're decorating
3. Accept the decorated object as a constructor parameter

```php app/Cache/CacheRepository.php
use Tempest\Container\Decorates;

#[Decorates(Repository::class)]
final readonly class CacheRepository implements Repository
{
    public function __construct(
        private Repository $repository,
        private Cache $cache,
    ) {}

    public function findById(int $id): ?Book
    {
        return $this->cache->resolve(
            key: "book.{$id}",
            callback: fn () => $this->repository->find($id)
        );
    }

    public function save(Book $book): Book
    {
        $this->cache->delete("book.{$book->id}");

        return $this->repository->save($book);
    }
}
```

When you request the `Repository` from the container, Tempest will automatically wrap the original implementation with your decorator. The decorated object (the original `Repository`) is injected into the decorator's constructor.

:::info
Decorators are discovered automatically through Tempest's [discovery](./05-discovery.md), so you don't need to manually register them.
:::

## Proxy loading

The container supports lazy loading of dependencies using the `#[Proxy]` attribute. Using this attribute on a property (that has `#[Inject]`) or a constructor parameter
will allow the container to instead inject a lazy proxy.
Since lazy proxies are transparent to the consumer you do not need to change anything else in your code.
The primary use case for this are heavy dependencies that may or may not be used.

```php app/BookController.php
use Tempest\Container\Proxy;

final readonly class BookController
{
    public function __construct(
        #[Proxy]
        private VerySlowClass $verySlowClass
    ) { /* … */ }
}
```
