---
title: "Routing"
description: "Learn how to route requests to controllers. In Tempest, this is done using attributes, which are automatically discovered by the framework."
---

## Overview

In Tempest, you may associate a route to any class method. Usually, this is done in dedicated controller classes, but it could be any class of your choice.

Tempest provides many attributes, named after HTTP verbs, to attach URIs to controller actions. These attributes implement the {`Tempest\Router\Route`} interface, so you can write your own if you need to.

```php app/HomeController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\view;

final readonly class HomeController
{
    #[Get(uri: '/home')]
    public function __invoke(): View
    {
        return view('home.view.php');
    }
}
```

Out of the box, an attribute for every HTTP verb is available: {b`Tempest\Router\Get`}, {b`Tempest\Router\Post`}, {b`Tempest\Router\Delete`}, {b`Tempest\Router\Put`}, {b`Tempest\Router\Patch`}, {b`Tempest\Router\Options`}, {b`Tempest\Router\Connect`}, {b`Tempest\Router\Trace`} and {b`Tempest\Router\Head`}.

## Route parameters

You may define dynamic segments in your route URIs by wrapping them in curly braces. The segment name inside the braces will be passed as a parameter to your controller method.

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\view;

final readonly class AircraftController
{
    #[Get(uri: '/aircraft/{id}')]
    public function show(int $id): View
    {
        // Fetch the aircraft by ID
        $aircraft = $this->aircraftRepository->getAircraftById($id);

        // Pass the aircraft to the view
        return view('aircraft.view.php', aircraft: $aircraft);
    }
}
```

### Regular expression constraints

You may constrain the format of a route parameter by specifying a regular expression after its name.

For instance, you may only accept numeric identifiers for an `id` parameter by using the following dynamic segment: `{regex}{id:[0-9]+}`. In practice, a route may look like this:

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\view;

final readonly class AircraftController
{
    #[Get(uri: '/aircraft/{id:[0-9]+}')]
    public function showAircraft(int $id): View
    {
        // …
    }
}
```

### Route binding

In controller actions, you may want to receive an object instead of a scalar value such as an identifier. This is especially useful in the case of [models](./03-database.md#models) to avoid having to write the fetching logic in each controller.

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\Http\Response;
use App\Aircraft;

final class AircraftController
{
    #[Get('/aircraft/{aircraft}')]
    public function show(Aircraft $aircraft): Response { /* … */ }
}
```

Route binding may be enabled for any class that implements the {`Tempest\Router\Bindable`} interface, which requires a `resolve()` method responsible for returning the correct instance.

```php
use Tempest\Router\Bindable;
use Tempest\Database\IsDatabaseModel;

final class Aircraft implements Bindable
{
    use IsDatabaseModel;

    public function resolve(string $input): self
    {
        return self::find(id: $input);
    }
}
```

By default, `Bindable` objects will be cast to strings when they are passed into the `uri()` function as a route parameter. You can override this default behaviour by tagging a public property on the object with the {b`\Tempest\Router\IsBindingValue`} attribute:

```php
use Tempest\Router\Bindable;
use Tempest\Router\IsBindingValue;

final class Aircraft implements Bindable
{
    #[IsBindingValue]
    public string $callSign;

    public function resolve(string $input): self
    {
        return self::find(id: $input);
    }
}
```

### Backed enum binding

You may inject string-backed enumerations to controller actions. Tempest will try to map the corresponding parameter from the URI to an instance of that enum using the [`tryFrom`](https://www.php.net/manual/en/backedenum.tryfrom.php) enum method.

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\Http\Response;
use App\AircraftType;

final readonly class AircraftController
{
    #[Get('/aircraft/{type}')]
    public function show(AircraftType $type): Response { /* … */ }
}
```

In the example above, we inject an `AircraftType` enumeration. If the request's `type` parameter has a value specified in that enumeration, it will be passed to the controller action. Otherwise, a HTTP 404 response will be returned without entering the controller method.

```php app/AircraftType.php
enum AircraftType: string
{
    case PC12 = 'pc12';
    case PC24 = 'pc24';
    case SF50 = 'sf50';
}
```

### Regex parameters

You may use regular expressions to match route parameters. This can be useful to create catch-all routes or to match a route parameter to any kind of regex pattern. Add a colon `:` followed by a pattern to the parameter's name to indicate that it should be matched using a regular expression.

```php
#[Get('/main/{path:.*}')]
public function docsRedirect(string $path): Redirect
{
    // …
}
```

## Generating URIs

Tempest provides a `\Tempest\uri` function that can be used to generate a URI to a controller method. This function accepts the FQCN of the controller or a callable to a method as its first argument, and named parameters as [the rest of its arguments](https://www.php.net/manual/en/functions.arguments.php#functions.variable-arg-list).

```php
use function Tempest\Router\uri;

// Invokable classes can be referenced directly:
uri(HomeController::class);
// /home

// Classes with named methods are referenced using an array
uri([AircraftController::class, 'store']);
// /aircraft

// Additional URI parameters are passed in as named arguments:
uri([AircraftController::class, 'show'], id: $aircraft->id);
// /aircraft/1
```

:::info
URI-related methods are also available by injecting the {b`Tempest\Router\UriGenerator`} class into your controller.
:::

### Signed URIs

A signed URI may be used to ensure that the URI was not modified after it was created. This is useful for implementing login links, or other endpoints that need protection against tampering.

To create a signed URI, you may use the `signed_uri` function. This function accepts the same arguments as `uri`, and returns the URI with a `signature` parameter:

```php
use function Tempest\Router\signed_uri;

signed_uri(
    action: [MailingListController::class, 'unsubscribe'],
    email: $email
);
```

Alternatively, you may use `temporary_signed_uri` to provide a duration after which the signed URI will expire, providing an extra layer of security.

```php
use function Tempest\Router\temporary_signed_uri;

temporary_signed_uri(
    action: PasswordlessAuthenticationController::class,
    duration: Duration::minutes(10),
    userId: $userId
);
```

To ensure the validity of a signed URL, you should call the `hasValidSignature` method on the {`Tempest\Router\UriGenerator`} class.

```php
final class PasswordlessAuthenticationController
{
    public function __construct(
        private readonly UriGenerator $uri,
    ) {}

    public function __invoke(Request $request): Response
    {
        if (! $this->uri->hasValidSignature($request)) {
            return new Invalid();
        }

        // ...
    }
}
```

### Matching the current URI

To determine whether the current request matches a specific controller action, Tempest provides the `is_current_uri` function. This function accepts the same arguments as `uri`, and returns a boolean.

```php
use function Tempest\Router\is_current_uri;

// Current URI is: /aircraft/1

// Providing no argument to the right controller action will match
is_current_uri(AircraftController::class); // true

// Providing the correct arguments to the right controller action will match
is_current_uri(AircraftController::class, id: 1); // true

// Providing invalid arguments to the right controller action will not match
is_current_uri(AircraftController::class, id: 2); // false
```

## Accessing request data

A core pattern of any web application is to access data from the current request. You may do so by injecting {`Tempest\Http\Request`} to a controller action. This class provides access to the request's body, query parameters, method, and other attributes through dedicated class properties.

### Using request classes

In most situations, the data you expect to receive from a request is structured. You expect clients to send specific values, and you want them to follow specific rules.

The idiomatic way to achieve this is by using request classes. They are classes with public properties that correspond to the data you want to retrieve from the request. Tempest will automatically validate these properties using PHP's type system, in addition to optional [validation attributes](../2-features/06-validation) if needed.

A request class must implement {`Tempest\Http\Request`} and should use the {`Tempest\Http\IsRequest`} trait, which provides the default implementation.

```php app/RegisterAirportRequest.php
use Tempest\Http\Request;
use Tempest\Http\IsRequest;
use Tempest\Validation\Rules\Length;

final class RegisterAirportRequest implements Request
{
    use IsRequest;

    #[Length(min: 10, max: 120)]
    public string $name;

    public ?DateTimeImmutable $registeredAt = null;

    public string $servedCity;
}
```

:::info Interfaces with default implementations
Tempest uses this pattern a lot. Most classes that interact with the framework need to implement an interface, and a corresponding trait with a default implementation will be provided.
:::

Once you have created a request class, you may simply inject it into a controller action. Tempest will take care of filling its properties and validating them, leaving you with a properly-typed object to work with.

```php app/AirportController.php
use Tempest\Router\Post;
use Tempest\Http\Responses\Redirect;
use function Tempest\map;
use function Tempest\Router\uri;

final readonly class AirportController
{
    #[Post(uri: '/airports/register')]
    public function store(RegisterAirportRequest $request): Redirect
    {
        $airport = map($request)->to(Airport::class)->save();

        return new Redirect(uri([self::class, 'show'], id: $airport->id));
    }
}
```

:::info A note on data mapping
The `map()` function allows mapping any data from any source into objects of your choice. You may read more about them in [their documentation](../2-features/01-mapper.md).
:::

### Retrieving data directly

For simpler use cases, you may simply retrieve a value from the body or the query parameter using the request's `get` method.

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\Http\Request;

final readonly class AircraftController
{
    #[Get(uri: '/aircraft')]
    public function me(Request $request): View
    {
        $icao = $request->get('icao');
        // …
    }
}
```

## Form validation

Oftentimes you'll want to submit form data from a website to be processed in the backend. In the previous section we've explained that Tempest will automatically map and validate request data unto request objects, but how do you then show validation errors back on the frontend?

Whenever a validation error occurs, Tempest will redirect back to the page the request was submitted on, or send a 400 invalid response (in case you're sending API requests). The validation errors can be found in two places:

- As a JSON encoded string in the `{txt}X-Validation` header
- Within the session with the `Session::VALIDATION_ERRORS` key

The JSON encoded header is available for when you're building APIs with Tempest. The session errors are available for when you're building web pages. For web pages, you also need a way to show the errors when they occur; Tempest comes with some built-in view components to help you with that.

```html
<x-form :action="uri(StorePostController::class)">
    <x-input name="name" />
    
    <x-input type="email" name="email" />
    
    <x-submit />
</x-form>
```

`{html}<x-form>` is a view component that will automatically include the CSRF token, as well as default to sending `POST` requests. `{html}<x-input>` is a view component that renders a label, input field, and validation errors all at once. In practice, you'll likely want to make changes to these built-in view components. That's why you can run `./tempest install view-components` and select the components you want to pull into your project. You can [read more about installing view components here](/2.x/essentials/views#built-in-components).

## Route middleware

Middleware can be applied to handle tasks in between receiving a request and sending a response. To specify a middleware for a route, add it to the `middleware` argument of a route attribute.

```php app/ReceiveInteractionController.php
use Tempest\Router\Get;
use Tempest\Http\Response;

final readonly class ReceiveInteractionController
{
    #[Post('/slack/interaction', middleware: [ValidateWebhook::class])]
    public function __invoke(): Response
    {
        // …
    }
}
```

The middleware class must be an invokable class that implements the {`Tempest\Router\HttpMiddleware`} interface. This interface has an `{:hl-property:__invoke:}()` method that accepts the current request as its first parameter and {`Tempest\Router\HttpMiddlewareCallable`} as its second parameter.

`HttpMiddlewareCallable` is an invokable class that forwards the `$request` to its next step in the pipeline.

```php
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Core\Priority;

#[SkipDiscovery]
#[Priority(Priority::LOW)]
final readonly class ValidateWebhook implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $signature = $request->headers->get('X-Slack-Signature');
        $timestamp = $request->headers->get('X-Slack-Request-Timestamp');

        // …

        return $next($request);
    }
}
```

### Middleware priority

All middleware classes get sorted based on their priority. By default, each middleware gets the "normal" priority, but you can override it using the `#[Priority]` attribute:

```php
use Tempest\Core\Priority;

#[Priority(Priority::HIGH)]
final readonly class ValidateWebhook implements HttpMiddleware
{ /* … */ }
```

Note that priority is defined using an integer. You can however use one of the built-in `Priority` constants: `Priority::FRAMEWORK`, `Priority::HIGHEST`, `Priority::HIGH`, `Priority::NORMAL`, `Priority::LOW`, `Priority::LOWEST`.

### Middleware discovery

Global middleware classes are discovered and sorted based on their priority. You can make a middleware class non-global by adding the {b`#[Tempest\Discovery\SkipDiscovery]`} attribute:

```php
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class ValidateWebhook implements HttpMiddleware
{ /* … */ }
```

### Excluding route middleware

Some routes may not require specific global middleware to be applied. For instance, API routes do not need CSRF protection. You may skip specific middleware by using the `without` argument of the route attribute.

```php app/Slack/ReceiveInteractionController.php
use Tempest\Router\Post;
use Tempest\Http\Response;

final readonly class ReceiveInteractionController
{
    #[Post('/slack/interaction', without: [VerifyCsrfMiddleware::class, SetCookieMiddleware::class])]
    public function __invoke(): Response
    {
        // …
    }
}
```

## Route decorators (route groups)

Route decorators are Tempest's way to manage routes in bulk; it's a feature similar to route groups in other frameworks. Route decorators are attributes that implement the {b`\Tempest\Router\RouteDecorator`} interface. A route decorator's task is to make changes or add functionality to whether route it's associated with. Tempest comes with a few built-in route decorators, and you can make your own as well.

In most cases, you'll want to add route decorators to a controller class, so that they are applied to all actions of that class:

```php
use Tempest\Router\Prefix;
use Tempest\Router\Get;

#[Prefix('/api')]
final readonly class ApiController
{
    #[Get('/books')]
    public function books(): Response { /* … */ }
    
    #[Get('/authors')]
    public function authors(): Response { /* … */ }
}
```

However, route decorators may also be applied to individual controller actions:

```php
use Tempest\Router\Stateless;
use Tempest\Router\Get;

final readonly class BlogPostController
{
    #[Stateless]
    #[Get('/rss')]
    public function rss(): Response { /* … */ }
}
```

### Built-in route decorators

These route decorators are provided by Tempest:

#### `#[Stateless]`

When you're building API endpoints, RSS feeds, or any other kind of page that does not require any cookie or session data, you may use the {b`#[Tempest\Router\Stateless]`} attribute, which will remove all state-related logic:

```php
use Tempest\Router\Stateless;
use Tempest\Router\Get;

final readonly class BlogPostController
{
    #[Stateless]
    #[Get('/rss')]
    public function rss(): Response { /* … */ }
}
```

#### `#[Prefix]`

Adds a prefix to all associated routes.

```php
use Tempest\Router\Prefix;
use Tempest\Router\Get;

#[Prefix('/api')]
final readonly class ApiController
{
    #[Get('/books')]
    public function books(): Response { /* … */ }
    
    #[Get('/authors')]
    public function authors(): Response { /* … */ }
}
```

#### `#[WithMiddleware]`

Adds middleware to all associated routes.

```php
use Tempest\Router\WithMiddleware;
use Tempest\Router\Get;

#[Middleware(AuthMiddleware::class, AdminMiddleware::class)]
final readonly class AdminController { /* … */ }
```

#### `#[WithoutMiddleware]`

Explicitly removes middleware to all associated routes.

```php
use Tempest\Router\WithoutMiddleware;
use Tempest\Router\Get;

#[WithoutMiddleware(VerifyCsrfMiddleware::class, SetCookieMiddleware::class)]
final readonly class StatelessController { /* … */ }
```

### Custom route decorators

Building your own route decorators is done by implementing the {b`\Tempest\Router\RouteDecorator`} interface and marking your decorator as an attribute.

```php
use Attribute;
use Tempest\Router\RouteDecorator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class Auth implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        $route->middleare[] = AuthMiddleware::class;

        return $route;
    }
}
```

## Responses

All requests to a controller action expect a response to be returned to the client. This is done by returning a `{php}View` or a `{php}Response` object.

### View responses

Returning a view is a shorthand for returning a successful response with that view. You may as well use the `{php}view()` function directly to construct a view.

```php app/Aircraft/AircraftController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\view;

final readonly class AircraftController
{
    #[Get(uri: '/aircraft/{aircraft}')]
    public function show(Aircraft $aircraft, User $user): View
    {
        return view('./show.view.php',
            aircraft: $aircraft,
            user: $user,
        );
    }
}
```

Tempest has a powerful templating system inspired by modern front-end frameworks. You may read more about views in their [dedicated chapter](./02-views.md).

### Using built-in response classes

Tempest provides several classes, all implementing the {`Tempest\Http\Response`} interface, mostly named after HTTP statuses.

- `{php}Ok` — the 200 response. Accepts an optional body.
- `{php}Created` — the 201 response. Accepts an optional body.
- `{php}Redirect` — redirects to the specified URI.
- `{php}Back` — redirects to previous page, accepts a fallback.
- `{php}Download` — downloads a file from the browser.
- `{php}File` — shows a file in the browser.
- `{php}Invalid` — a response with form validation errors, redirecting to the previous page.
- `{php}NotFound` — the 404 response. Accepts an optional body.
- `{php}ServerError` — a 500 server error response.

The following example conditionnally returns a `Redirect`, otherwise letting the user download a file by sending a `Download` response:

```php app/FlightPlanController.php
use Tempest\Router\Get;
use Tempest\Http\Responses\Download;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Response;

final readonly class FlightPlanController
{
    #[Get('/{flight}/flight-plan/download')]
    public function download(Flight $flight): Response
    {
        $allowed = /* … */;

        if (! $allowed) {
            return new Redirect('/');
        }

        return new Download($flight->flight_plan_path);
    }
}
```

### Sending generic responses

It might happen that you need to dynamically compute the response's status code, and would rather not use a condition to send the corresponding response object.

You may then return an instance of {`Tempest\Http\GenericResponse`}, specifying the status code and an optional body.

```php app/CreateFlightController.php
use Tempest\Router\Get;
use Tempest\Http\Responses\Download;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\GenericResponse;
use Tempest\Http\Response;

final readonly class CreateFlightController
{
    #[Post('/{flight}')]
    public function __invoke(Flight $flight): Response
    {
        $status = /* … */
        $body = /* … */

        return new GenericResponse(
            status: $status,
            body: $body,
        );
    }
}
```

### Using custom response classes

There are situations where you might send the same kind of response in a lot of places, or you might want to have a proper API for sending a structured response.

You may create your own response class by implementing {`Tempest\Http\Response`}, which default implementation is provided by the {`Tempest\Http\IsResponse`} trait:

```php app/AircraftRegistered.php
use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;

final class AircraftRegistered implements Response
{
    use IsResponse;

    public function __construct(Aircraft $aircraft)
    {
        $this->status = Status::CREATED;
        $this->flash(
            key: 'success',
            value: "Aircraft {$aircraft->icao_code} was successfully registered."
        );
    }
}
```

### Specifying content types

Tempest is able to automatically infer the response's content type, usually inferred from the request's `Accept` header.

However, you may override the content type manually by specifying the `setContentType` method on `Response` clases. This method accepts a case of {`Tempest\Router\ContentType`}.

```php app/JsonController.php
use Tempest\Router\Get;
use Tempest\Router\ContentType;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;

final readonly class JsonController
{
    #[Get('/json')]
    public function json(string $path): Response
    {
        $data = [ /* … */ ];

        return new Ok($data)->setContentType(ContentType::JSON);
    }
}
```

### Post-processing responses

There are some situations in which you may need to act on a response right before it is sent to the client. For instance, you may want to display custom error error pages when an exception occurred, or redirect somewhere instead of displaying the [built-in HTTP 404](/hello-from-the-void){:ssg-ignore="true"} page.

This may be done using a response processor. Similar to [view processors](./02-views.md#pre-processing-views), they are classes that implement the {`Tempest\Response\ResponseProcessor`} interface. In the `process()` method, you may mutate and return the response object:

```php app/ErrorResponseProcessor.php
use function Tempest\view;

final class ErrorResponseProcessor implements ResponseProcessor
{
    public function process(Response $response): Response
    {
        if (! $response->status->isSuccessful()) {
            return $response->setBody(view('./error.view.php', status: $response->status));
        }

        return $response;
    }
}
```

## Custom route attributes

It is often a requirement to have a bunch of routes following the same specifications—for instance, using the same middleware, or the same URI prefix.

To achieve this, you may create your own route attribute, implementing the {`Tempest\Router\Route`} interface. The constructor of the attribute may hold the logic you want to apply to the routes using it.

```php app/RestrictedRoute.php
use Attribute;
use Tempest\Http\Method;
use Tempest\Router\Route;

#[Attribute]
final readonly class RestrictedRoute implements Route
{
    public function __construct(
        public string $uri,
        public Method $method,
        public array $middleware,
    ) {
        $this->uri = $uri;
        $this->method = $method;
        $this->middleware = [
            AuthorizeUserMiddleware::class,
            LogUserActionsMiddleware::class,
            ...$middleware,
        ];
    }
}
```

This attribute can be used in place of the usual route attributes, on controller action methods.

## Session management

Sessions in Tempest are managed by the {b`Tempest\Http\Session\Session`} class. You can inject it anywhere you need it. As soon as the `Session` is injected, it will be started behind the scenes.

```php
use Tempest\Http\Session\Session;

final readonly class TodoController
{
    public function __construct(
        private Session $session,
    ) {}

    #[Post('/select/{todo}']
    public function select(Todo $todo): View
    {
        if ($this->session->get('selected_todo') === $todo->id) {
            $this->session->remove('selected_todo');
        } else {
            $this->session->set('selected_todo', $todo->id);
        }

        return $this->list();
    }
}
```

### Flashing values

When you need to "flash" something to the user — in other words: show something once and clear if after refresh — you can use the `flash()` method on the session:

```php
public function store(Todo $todo): Redirect
{
    $this->session->flash('message', 'Save was successful');
    
    return new Redirect('/');
}
```

### Session configuration

Tempest supports file and database-based sessions, the former being the default option. Sessions can be configured by creating a `session.config.php` file, in which the expiration time and the session driver can be specified.

#### File sessions

When using file-based sessions, which is the default, session data will be stored in files within the specified directory, relative to `.tempest`. You may configure the path and expiration duration like so:

```php app/Config/session.config.php
use Tempest\Http\Session\Config\FileSessionConfig;
use Tempest\DateTime\Duration;

return new FileSessionConfig(
   expiration: Duration::days(30),
   path: 'sessions',
);
```

#### Database sessions

Tempest provides a database-based session driver, particularly useful for applications that run on multiple servers, as the session data can be shared across all instances.

Before using database sessions, a dedicated table is needed. Tempest provides a migration, which may be installed in your project using its installer:

```sh
./tempest install sessions:database
```

This installer will also suggest creating the configuration file that sets up database sessions, with a default expiration of 30 days:

```php app/Sessions/session.config.php
use Tempest\Http\Session\Config\DatabaseSessionConfig;
use Tempest\DateTime\Duration;

return new DatabaseSessionConfig(
    expiration: Duration::days(30),
);
```

### Session cleaning

Sessions expire based on the last activity time. This means that as long as a user is actively using your application, their session will remain valid.

Outdated sessions must occasionally be cleaned up. Tempest comes with a built-in command to do so, `session:clean`. This command makes use of the [scheduler](/2.x/features/scheduling). If you have scheduling enabled, it will automatically run behind the scenes.

## Deferring tasks

It is sometimes needed, during requests, to perform tasks that would take a few seconds to complete. This could be sending an email, or keeping track of a page visit.

Tempest provides a way to perform that task after the response has been sent, so the client doesn't have to wait until its completion. This is done by passing a callback to the `defer` function:

```php app/TrackVisitMiddleware.php
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Http\Request;
use Tempest\Http\Response;

use function Tempest\defer;
use function Tempest\event;

final readonly class TrackVisitMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        defer(fn () => event(new PageVisited($request->getUri())));

        return $next($request);
    }
}
```

The `defer` callback may accept any parameter that the container can inject.

:::warning
Task deferring only works if [`fastcgi_finish_request()`](https://www.php.net/manual/en/function.fastcgi-finish-request.php) is available within your PHP installation. If it's not available, deferred tasks will still be run, but the client response will only complete after all tasks have been finished.
:::

## Testing

Tempest provides a router testing utility accessible through the `http` property of the [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php) test case. You may learn more about testing in the [dedicated chapter](./07-testing.md).

The router testing utility provides methods for all HTTP verbs. These method return an instance of [`TestResponseHelper`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/Http/TestResponseHelper.php), giving access to multiple assertion methods.

```php tests/ProfileControllerTest.php
final class ProfileControllerTest extends IntegrationTestCase
{
    public function test_can_render_profile(): void
    {
        $response = $this->http
            ->get('/account/profile')
            ->assertOk()
            ->assertSee('My Profile');
    }
}
```
