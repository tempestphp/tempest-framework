# Tempest is an MVC micro framework that gets out of your way.

## Installation

You can install tempest as a web project using `composer create-project`:

```php
composer create-project tempest/app <project-name>
cd <project-name>
npm run dev
```

Or you could require the standalone framework in any existing project:

```php
composer require tempest/framework
```

Optionally, you can interactively install Tempest's entry points: `index.php` and/or `tempest.php`:

```php
php vendor/bin/tempest.php install
```