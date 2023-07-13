Tempest views are plain PHP files. Every view has access to its data via `$this` calls. By adding an `@var` docblock to your view files, you'll get static insights and autocompletion.

```php
// View/home.php

Hello, <?= $this->name ?>
```

```php
final readonly class HomeController
{
    #[Get(uri: '/home')]
    public function __invoke(): View
    {
        return view('Views/home.php')
            ->data(
                name: 'Brent',
            )
    }
}
```

You can extend from other views like so:

```php
<?php 
// View/home.php

/** @var \Tempest\View\GenericView $this */ 
$this->extendsPath = 'View/base.php';
?>

Hello, <?= $this->name ?>
```

The base view, in turn, can look like this, where `$this->slot` is the content from the child's view.

```php
<?php /** @var \Tempest\View\GenericView $this */?>
<html lang="en">
<head>
    <title><?= $this->title ?? 'Home' ?></title>
</head>
<body>
<?= $this->slot ?? '' ?>
</body>
</html>
```

### View Models

Calling the `view()` helper function in controllers means you'll use the `GenericView` implementation provided by Tempest.

Many views however might benefit by using a dedicated class — a View Model. View Models will provide improved static insights both in your controllers and view files, and will allow you to expose custom methods to your views.

A View Model is a class the implements `View`, it can optionally set a path to a fixed view file, and provide data in its constructor. 

```php
final class HomeView implements View
{
    use BaseView;

    public function __construct(
        public readonly string $name,
    ) {
        $this->path = 'Modules/Home/home.php';
    }
}
```

Once you've made a View Model, you can use it in your controllers like so:

```php
final readonly class HomeController
{
    #[Get(uri: '/')]
    public function __invoke(): HomeView
    {
        return new HomeView(
            name: 'Brent',
        );
    }
}
```

Its view file would look like this:

```php
<?php
/** @var \App\Modules\Home\HomeView $this */

$this->extendsPath = 'Views/base.php';
?>

Hello, <?= $this->name ?>
```

Note that you could also set the `extend` parameter within the View Model:

```php
final class HomeView implements View
{
    use BaseView;

    public function __construct(
        public readonly string $name,
    ) {
        $this->path = 'Modules/Home/home.php';
        $this->extends = 'Views/base.php';
    }
}
```

So that its view file would look like this:

```php
<?php /** @var \App\Modules\Home\HomeView $this */ ?>

Hello, <?= $this->name ?>
```

On top of that, View Models can expose methods to view files:

```php
final class BlogPostView implements View
{
    // …
    
    public function formatDate(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d');
    }   
}
```

Which can be used like so:

```php
<?php /** @var \App\Modules\Home\HomeView $this */ ?>

<?= $this->formatDate($post->date) ?>
```

View Models are an excellent way of moving view-related complexity away from the controller, while simultaneously improving static insights.