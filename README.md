<p align="center">
  <a href="https://github.com/tempestphp/tempest-framework" target="_blank">

![](.github/tempest-logo.svg)
  </a>
</p>
<p align="center">
    <a href="LICENSE" target="_blank">
        <img alt="Software License" src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square">
    </a> 
    <a href="https://coveralls.io/github/tempestphp/tempest-framework?branch=main" target="_blank">
        <img alt="Coverage Status" src="https://coveralls.io/repos/github/tempestphp/tempest-framework/badge.svg?branch=main">
    </a>
</p>

[TempestPHP](https://github.com/tempestphp/tempest-framework) is a PHP MVC micro-framework that gets out of your way. Its design philosophy is that developers should write as little framework-related code as possible, so that they can focus on application code instead. Our primary goal is to provide a structured framework that enables PHP users at all levels to rapidly develop robust web applications, without any loss to flexibility.


## Installing TempestPHP via Composer

You can install TempestPHP into your project using [Composer](https://getcomposer.org).  If you're starting a new project, we recommend using the [app scaffold](https://github.com/tempestphp/tempest-app) as a starting point. For existing applications you can run the following:

``` bash
composer create-project tempest/app <project-name>
```

This project scaffold includes a basic frontend setup including tailwind:

``` bash
cd <project-name>
npm run dev
```

### TempestPHP as a Package

If you don't need an app scaffold, you can opt to install `tempest/framework` as a standalone package. You could do this in any project; it could already contain code, or it could be an empty project.

``` bash
composer require tempest/framework
```

Installing TempestPHP this way will give you access to the tempest console as a composer binary:

```
./vendor/bin/tempest
```

Optionally, you can choose to install Tempest's entry points in your project:

```
./vendor/bin/tempest install
```

Installing TempestPHP into a project means that it will copy one or two files into that project:

- `public/index.php` — the web application entry point
- `tempest` – the console application entry point

You can choose which files you want to install, and you can always rerun the `install` command at a later point in time.


## Running Tests

Assuming you have PHPUnit installed system wide using one of the methods stated [here](https://phpunit.de/manual/current/en/installation.html), you can run the tests for tempestphp by doing the following:

1. Copy `phpunit.xml.dist` to `phpunit.xml`.
2. Add the relevant database credentials to your `phpunit.xml` if you want to run tests against a non-SQLite datasource.
3. Run `phpunit`.


## Some Handy Links

* [TempestPHP](https://github.com/tempestphp/tempest-framework) - The rapid development PHP framework.


## Get Support!

* [GitHub Issues](https://github.com/tempestphp/tempest-framework/issues) - Got issues? Please tell us!
* [GitHub Pull Requests](https://github.com/tempestphp/tempest-framework/pulls) - Want to contribute? Get involved!


## Contributing

* [CONTRIBUTING.md](https://github.com/tempestphp/tempest-framework/blob/master/CONTRIBUTING.md) - Quick pointers for contributing to the TempestPHP project.


## Security

Issues are used to track todos, bugs, feature requests, and more. As issues are created, they’ll appear here in a searchable and filterable list. To get started, you should [create an issue](https://github.com/tempestphp/tempest-framework/issues/new/choose). 


## 

# Read how to get started with TempestPHP [here](https://github.com/tempestphp/tempest-docs/blob/master/index.md).