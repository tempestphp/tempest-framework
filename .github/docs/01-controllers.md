```php
final readonly class HomeController
{
    #[Get(uri: '/')]
    public function __invoke(): View
    {
        return view('View/home.php')
            ->data(
                title: 'Home',
                name: 'Brent',
            );
    }
}
```

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

```php
// View/home.php

<?php 
/** @var App\View\HomeView $this */ 
$this->extends = 'View/base.php';
?>
```