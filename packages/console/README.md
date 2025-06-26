# A revolutionary way of building console applications in PHP.

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

[Get started here](https://tempestphp.com/console)

[Join the Tempest Discord](https://discord.gg/pPhpTGUMPQ)
