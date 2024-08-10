# The PHP framework that gets out of your way.
[![Coverage Status](https://coveralls.io/repos/github/tempestphp/tempest-framework/badge.svg?branch=main)](https://coveralls.io/github/tempestphp/tempest-framework?branch=main)

Read how to get started with Tempest [here](https://tempest.stitcher.io).

Zero config, zero overhead. This is Tempest:

```php
final readonly class BookController
{
    #[Get('/blog')]
    public function index() { /* … */ }
    
    #[Get('/blog/{post}')]
    public function show(Post $post) { /* … */ }
}

final readonly class RssSyncCommand
{
    public function __construct(private Console $console) {}

    #[ConsoleCommand('rss:sync')]
    public function __invoke(bool $force = false)  { /* … */ }
}
```

# Contributing
We welcome contributing to the Tempest framework! We only ask that you take a quick look at our [guidelines](.github/CONTRIBUTING.md) and then head on over to the issues page to see some ways you might help out!

[Join the Tempest Discord](https://discord.gg/pPhpTGUMPQ)
