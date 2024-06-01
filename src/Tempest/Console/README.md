# Frictionless command line interfaces with PHP

```
composer require tempest/console
```

```php
final readonly class Hello
{
    public function __construct(private Console $console) {}

    #[ConsoleCommand]
    public function world(): void
    {
        $this->console->writeln('Hello World!');
    }
}
```

Read the docs: [https://tempest.stitcher.io/console/01-getting-started](https://tempest.stitcher.io/console/01-getting-started).

[Join the Tempest Discord](https://discord.gg/pPhpTGUMPQ)