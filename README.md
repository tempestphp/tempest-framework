<p align="center">
  <a href="https://github.com/tempestphp/tempest-framework" target="_blank" >

![](img/tempestphp_logo.png)

    <!-- <img alt="tempestphp" src="https://github.com/tempestphp/tempest-framework/img/logos/tempestphp_logo.png" width="400" /> -->

  </a>
</p>
<p align="center">
    <a href="LICENSE" target="_blank">
        <img alt="Software License" src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square">
    </a>
    <a href="https://phpstan.org/" target="_blank">
        <img alt="PHPStan" src="https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat">
    </a>
    <a href="https://packagist.org/packages/tempestphp/tempestphp" target="_blank">
        <img alt="Total Downloads" src="https://img.shields.io/packagist/dt/tempestphp/tempestphp.svg?style=flat-square">
    </a>
    <a href="https://packagist.org/packages/tempestphp/tempestphp" target="_blank">
        <img alt="Latest Stable Version" src="https://img.shields.io/packagist/v/tempestphp/tempestphp.svg?style=flat-square&label=stable">
    </a>
</p>

[TempestPHP](https://github.com/tempestphp/tempest-framework) is a PHP MVC micro-framework that gets out of your way. Its design philosophy is that developers should write as little framework-related code as possible, so that they can focus on application code instead. Our primary goal is to provide a structured framework that enables PHP users at all levels to rapidly develop robust web applications, without any loss to flexibility.


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
} -->


## Installing tempestphp via Composer

You can install tempestphp into your project using [Composer](https://getcomposer.org).  If you're starting a new project, we recommend using the [app skeleton](https://github.com/tempestphp/tempest-app) as a starting point. For existing applications you can run the following:

``` bash
composer create-project tempest/app my-app
```

This project scaffold includes a basic frontend setup including tailwind:

``` bash
npm run dev
```

### Tempest as a package

If you don't need an app scaffold, you can opt to install `tempest/framework` as a standalone package. You could do this in any project; it could already contain code, or it could be an empty project.

``` bash
composer require tempest/framework
```

Installing Tempest this way will give you access to the tempest console as a composer binary:

```
./vendor/bin/tempest
```

Optionally, you can choose to install Tempest's entry points in your project:

```
./vendor/bin/tempest install
```

Installing Tempest into a project means that it will copy one or two files into that project:

- `public/index.php` — the web application entry point
- `tempest` – the console application entry point

You can choose which files you want to install, and you can always rerun the `install` command at a later point in time.






For details on the (minimum/maximum) PHP version see [version map](https://github.com/tempestphp/tempest-frameworktempestphp/wiki#version-map).





## Running Tests

Assuming you have PHPUnit installed system wide using one of the methods stated [here](https://phpunit.de/manual/current/en/installation.html), you can run the tests for tempestphp by doing the following:

1. Copy `phpunit.xml.dist` to `phpunit.xml`.
2. Add the relevant database credentials to your `phpunit.xml` if you want to run tests against a non-SQLite datasource.
3. Run `phpunit`.

## Some Handy Links

* [TempestPHP](https://github.com/tempestphp/tempest-framework) - The rapid development PHP framework.
<!-- * [Plugins](https://plugins.tempestphp.org) - A repository of extensions to the framework. -->

## Get Support!

<!-- * [Slack](https://slack-invite.tempestphp.org/) - Join us on Slack. -->
<!-- * [Discord](https://discord.gg/k4trEMPebj) - Join us on Discord. -->
<!-- * [Forum](https://discourse.tempestphp.org/) - Official tempestphp forum. -->
* [GitHub Issues](https://github.com/tempestphp/tempest-framework/issues) - Got issues? Please tell us!
* [GitHub Pull Requests](https://github.com/tempestphp/tempest-framework/pulls) - Want to contribute? Get involved!

## Contributing

* [CONTRIBUTING.md](https://github.com/tempestphp/tempest-framework/CONTRIBUTING.md) - Quick pointers for contributing to the tempestphp project.

# Security

Issues are used to track todos, bugs, feature requests, and more. As issues are created, they’ll appear here in a searchable and filterable list. To get started, you should [create an issue](https://github.com/tempestphp/tempest-framework/issues/new/choose). 
 



## A basic Tempest project

Tempest won't impose any fixed file structure on you: one of the core principles of Tempest is that it will scan you project code for you, and it will automatically discover any files it needs to. For example: Tempest is able to differentiate between a controller method and a console command by looking at the code, instead of relying on naming conventions. This is what's called **discovery**, and it's one of Tempest's most powerful features. 

You can make a project that looks like this:

```
app
├── Console
│   └── RssSyncCommand.php
├── Controllers
│   ├── BlogPostController.php
│   └── HomeController.php
└── Views
    ├── blog.view.php
    └── home.view.php
```

Or a project that looks like this:

```
app
├── Blog
│   ├── BlogPostController.php
│   ├── RssSyncCommand.php
│   └── blog.view.php
└── Home
    ├── HomeController.php
    └── home.view.php
```

For Tempest, it's all the same.

Discovery works by scanning you project code, and looking at each file and method individually to determine what that code does. For production apps, Tempest will cache these results as PHP code, so there's absolutely no performance overhead to doing so.

As an example, Tempest is able to determine which methods are controller methods based on their route attributes:

```php
final readonly class BlogPostController
{
    #[Get('/blog')]
    public function index() 
    { /* … */ }
    
    #[Get('/blog/{post}')]
    public function show(Post $post) 
    { /* … */ }
}
```

And likewise, it's able to detect console commands based on their console command attribute:

```php
final readonly class RssSyncCommand
{
    public function __construct(private Console $console) {}

    #[ConsoleCommand('rss:sync')]
    public function __invoke(bool $force = false)  
    { /* … */ }
}
```

We'll cover controllers and console commands in depth in future chapters.
