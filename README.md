# The PHP framework that gets out of your way.

Tempest is a PHP framework that gets out of your way . Its design philosophy is that developers should write as little framework-related code as possible, so that they can focus on application code instead. Zero config, zero overhead. This is Tempest:

```php
final readonly class BookController
{
    #[Get('/books/{book}')]
    public function show(Book $book): Response
    {
        return new Ok($book);
    }

    #[Post('/books')]
    public function store(CreateBookRequest $request): Response
    {
        $book = map($request)->to(Book::class)->save();

        return new Redirect([self::class, 'show'], book: $book->id);
    }
    
    // …
}
```

```php
final readonly class MigrateUpCommand
{
    public function __construct(
        private Console $console,
        private MigrationManager $migrationManager,
    ) {}

    #[ConsoleCommand(
        name: 'migrate:up',
        description: 'Run all new migrations',
        middleware: [ForceMiddleware::class, CautionMiddleware::class],
    )]
    public function __invoke(): void
    {
        $this->migrationManager->up();

        $this->console->success("Everything migrated");
    }

    #[EventHandler]
    public function onMigrationMigrated(MigrationMigrated $migrationMigrated): void
    {
        $this->console->writeln("- {$migrationMigrated->name}");
    }
}
```

Read how to get started with Tempest [here](https://tempest.stitcher.io).

## Installation

Install Tempest in any project, including existing projects:

```
composer require tempest/framework:dev-main
```

Or create a Tempest project from scratch:

```
composer create-project tempest/app:dev-main <name>
```

Continue to read how Tempest works in [the docs](https://tempest.stitcher.io).

## Contributing
We welcome contributing to the Tempest framework! We only ask that you take a quick look at our [guidelines](.github/CONTRIBUTING.md) and then head on over to the issues page to see some ways you might help out!

For more information, [join the Tempest Discord](https://discord.gg/pPhpTGUMPQ)
