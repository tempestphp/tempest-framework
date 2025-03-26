<p align="center">
  <a href="https://tempestphp.com">
    <img src="https://github.com/tempestphp/.github/raw/refs/heads/main/.github/tempest-logo.svg" width="100" />
  </a>
</p>

<h1 align="center">Tempest</h1>
<div align="center">
  The PHP framework that lets you focus on your application code instead of framework quirks.
  <br />
  Read the <a href="https://tempestphp.com">documentation</a> to get started.
</div>

<br />
<br />

## Introduction

Tempest is a PHP framework that _gets out of your way_.

Its design philosophy is that developers should write as little framework-related code as possible, so that they can focus on application code instead.

Zero config, zero overhead. This is Tempest:

```php
final class BookController
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
#[Singleton]
final class MigrateUpCommand
{
    private int $count = 0;

    public function __construct(
        private readonly Console $console,
        private readonly MigrationManager $migrationManager,
    ) {}

    #[ConsoleCommand(
        name: 'migrate:up',
        description: 'Runs all new migrations',
        middleware: [ForceMiddleware::class, CautionMiddleware::class],
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'Validates the integrity of existing migration files by checking if they have been tampered with.')]
        bool $validate = false,
    ): ExitCode {
        if ($validate) {
            $validationSuccess = $this->console->call('migrate:validate');

            if ($validationSuccess !== 0 && $validationSuccess !== ExitCode::SUCCESS) {
                return ExitCode::INVALID;
            }
        }

        $this->migrationManager->up();

        $this->console
            ->success(sprintf('Migrated %s migrations', $this->count));

        return ExitCode::SUCCESS;
    }

    #[EventHandler]
    public function onMigrationMigrated(MigrationMigrated $event): void
    {
        $this->console->writeln("- {$event->name}");
        $this->count += 1;
    }
}
```

Read how to get started with Tempest [here](https://tempestphp.com).

&nbsp;

## Installation

Create a Tempest project from scratch:

```
composer create-project tempest/app:1.0-alpha.5 <name>
```

Or install Tempest in any existing project:

```
composer require tempest/framework:1.0-alpha.5
```

Continue to read how Tempest works in [the docs](https://tempestphp.com).

&nbsp;

## Contributing

We welcome contributing to Tempest! We only ask that you take a quick look at our [guidelines](https://tempestphp.com/docs/internals/contributing/).

An easy way to get started is to head on over to the issues page to see some ways you might help out.

<p align="center">
	<br />
	<br />
	<sub>
		Check out the <a href="https://tempestphp.com">documentation</a>
		&nbsp;
		·
		&nbsp;
		Join the <a href="https://tempestphp.com/discord">Discord</a> server
  </sub>
</p>
