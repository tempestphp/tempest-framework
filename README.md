# The PHP framework that gets out of your way.

Read how to get started with Tempest [here](https://github.com/tempestphp/tempest-docs/blob/master/01-getting-started.md).

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

## Contributing

We welcome your PRs and contributions. If you have any feature requests or bug reports, head over to the [issue page](https://github.com/tempestphp/tempest-framework/issues) and feel free to create an issue.

If you'd like to send PRs, you can check out and run Tempest locally like so:

```php
git clone git@github.com:tempestphp/tempest-framework.git
cd tempest-framework/
composer update
```

Before submitting PRs, run `composer qa` locally: 

```php
composer qa
```