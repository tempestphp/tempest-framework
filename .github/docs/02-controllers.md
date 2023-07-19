A controller action can be any class' method, as long as it's annotated with a `Route` attribute. Tempest offers some convenient Route attributes out of the box, and you can write your own if you need to.

```php
final readonly class HomeController
{
    #[Get(uri: '/home')]
    public function __invoke(): Response
    {
        return response()->ok();
    }
}
```

Route variables are mapped as method arguments:

```php
final readonly class BlogPostController
{
    #[Post(uri: '/blog/{id}/update')]
    public function store(int $id): Response
    {
        // …
        
        return response()->redirect(uri([BlogPostController::class, 'show'], id: $id)) 
    }
}
```

Request classes can be used to validate incoming data:

```php
final class BlogPostRequest implements Request
{
    use BaseRequest;
    
    #[Length(min: 10, max: 120)]
    public string $title;
    
    public ?DateTimeImmutable $publishedAt = null;
    
    public string $body;
}
```

```php
final readonly class BlogPostController
{
    #[Post(uri: '/blog/{id}/update')]
    public function store(int $id, BlogPostRequest $request): Response
    {
        // …
        
        return response()->redirect(uri([BlogPostController::class, 'show'], id: $id)) 
    }
}
```

### Custom routes

// TODO

```php
#[Attribute]
final class AdminRoute implements Route
{
    // …
}
```