---
title: "Routing"
description: "Learn how to route requests to controllers. In Tempest, this is done using attributes, which are automatically discovered by the framework."
---

## Overview

In Tempest, routes can be associated with any class method. This is typically done in dedicated controller classes, but any class can be used.

Tempest provides attributes, named after HTTP verbs, to attach URIs to controller actions. These attributes implement the {b`Tempest\Router\Route`} interface, allowing custom route attributes to be created.

```php app/HomeController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class HomeController
{
    #[Get(uri: '/home')]
    public function __invoke(): View
    {
        return view('./home.view.php');
    }
}
```

Out of the box, an attribute for every HTTP verb is available: {b`Tempest\Router\Get`}, {b`Tempest\Router\Post`}, {b`Tempest\Router\Delete`}, {b`Tempest\Router\Put`}, {b`Tempest\Router\Patch`}, {b`Tempest\Router\Options`}, {b`Tempest\Router\Connect`}, {b`Tempest\Router\Trace`} and {b`Tempest\Router\Head`}.

## Route parameters

Dynamic segments can be defined in route URIs by wrapping them in curly braces. The segment name inside the braces is passed as a parameter to the controller method.

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class AircraftController
{
    #[Get(uri: '/aircraft/{id}')]
    public function show(int $id): View
    {
        // Fetch the aircraft by ID
        $aircraft = $this->aircraftRepository->getAircraftById($id);

        // Pass the aircraft to the view
        return view('./aircraft.view.php', aircraft: $aircraft);
    }
}
```

### Optional parameters

A route can match both with and without a parameter. For instance, `/aircraft` can show all aircraft, while `/aircraft/123` shows a specific aircraft. This is achieved by marking route parameters as optional.

To mark a parameter as optional, prefix it with a question mark `?` inside the curly braces. The corresponding method parameter must either be nullable or have a default value.

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class AircraftController
{
    #[Get(uri: '/aircraft/{?id}')]
    public function index(?string $id): View
    {
        if ($id === null) {
            $aircraft = $this->aircraftRepository->all();
        } else {
            $aircraft = $this->aircraftRepository->find($id);
        }

        return view('aircraft.view.php', aircraft: $aircraft);
    }
}
```

In this example, both `/aircraft` and `/aircraft/123` match the same route. When the parameter is not provided, the method parameter receives `null`.

Alternatively, a default value can be provided instead of using a nullable type:

```php app/AircraftController.php
#[Get(uri: '/aircraft/{?type}')]
public function filter(string $type = 'all'): View
{
    // $type defaults to 'all' when not provided
    // $type is set to the provided value otherwise
}
```

Required and optional parameters can be combined. Optional parameters must come after required ones:

```php app/FlightController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class FlightController
{
    #[Get(uri: '/flights/{flightNumber}/{?segment}')]
    public function show(string $flightNumber, ?string $segment): View
    {
        // Matches both /flights/JFA123 and /flights/JFA123/departure
    }
}
```

Multiple optional parameters are also supported:

```php app/AircraftController.php
#[Get(uri: '/aircraft/{?manufacturer}/{?model}')]
public function search(?string $manufacturer, ?string $model): View
{
    // Matches /aircraft, /aircraft/pilatus, and /aircraft/pilatus/pc24
}
```

Optional parameters work with [regular expression constraints](#regular-expression-constraints). Add the regular expression after the parameter name:

```php app/AircraftController.php
#[Get(uri: '/aircraft/{?id:\d+}')]
public function show(?int $id): View
{
    // Matches /aircraft and /aircraft/123 (numeric only)
}
```

### Regular expression constraints

The format of a route parameter can be constrained by specifying a regular expression after its name.

For instance, to accept only numeric identifiers for an `id` parameter, use the following dynamic segment: `{regex}{id:[0-9]+}`. In practice, a route looks like this:

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

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

Controller actions can receive objects instead of scalar values such as identifiers. This is particularly useful for [models](./03-database.md#models) to avoid writing fetching logic in each controller.

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

Route binding can be enabled for any class that implements the {b`Tempest\Router\Bindable`} interface, which requires a static `resolve()` method responsible for returning the correct instance.

```php
use Tempest\Router\Bindable;
use Tempest\Database\IsDatabaseModel;

final class Aircraft implements Bindable
{
    public static function resolve(string $input): self
    {
        return query(self::class)->resolve($input);
    }
}
```

By default, {b`Tempest\Router\Bindable`} objects are cast to strings when passed into the {b`Tempest\Router\uri()`} function as a route parameter. This means that these objects should implement `Stringable`.

This default behaviour can be overridden by annotating a public property on the object with the {b`\Tempest\Router\IsBindingValue`} attribute:

:::code-group

```php app/Aircraft.php
use Tempest\Router\Bindable;
use Tempest\Router\IsBindingValue;

final class Aircraft implements Bindable
{
    #[IsBindingValue]
    public string $registrationNumber;

    public static function resolve(string $input): self
    {
        return query(self::class)
            ->where('registrationNumber', $input)
            ->first();
    }
}
```

```php "URI generation"
uri(ShowAircraftController::class, aircraft: $aircraft);
// → /aircraft/lxjfa
```

:::

### Backed enum binding

String-backed enumerations can be injected into controller actions. Tempest maps the corresponding parameter from the URI to an instance of that enum using the [`tryFrom`](https://www.php.net/manual/en/backedenum.tryfrom.php) enum method.

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

In the example above, an `AircraftType` enumeration is injected. If the request's `type` parameter has a value specified in that enumeration, it is passed to the controller action. Otherwise, an HTTP 404 response is returned without entering the controller method.

```php app/AircraftType.php
enum AircraftType: string
{
    case PC12 = 'pc12';
    case PC24 = 'pc24';
    case SF50 = 'sf50';
}
```

## Generating URIs

Tempest provides a {b`\Tempest\Router\uri()`} function to generate URIs to controller methods. This function accepts the fully-qualified class name of the controller or a callable to a method as its first argument, and named parameters as [the rest of its arguments](https://www.php.net/manual/en/functions.arguments.php#functions.variable-arg-list).

```php
use function Tempest\Router\uri;

// Invokable classes can be referenced directly:
uri(HomeController::class);
// → /home

// Classes with named methods are referenced using an array
uri([AircraftController::class, 'store']);
// → /aircraft

// Additional URI parameters are passed in as named arguments:
uri([AircraftController::class, 'show'], id: $aircraft->id);
// → /aircraft/1
```

:::info
URI-related methods are also available by injecting the {b`Tempest\Router\UriGenerator`} class into your controller.
:::

### Signed URIs

A signed URI ensures that the URI was not modified after it was created. This is useful for implementing login or unsubscribe links, or other endpoints that need protection against tampering.

To create a signed URI, use the {b`\Tempest\Router\signed_uri()`} function. This function accepts the same arguments as {b`\Tempest\Router\uri()`} and returns the URI with a `signature` parameter:

```php
use function Tempest\Router\signed_uri;

signed_uri(
    action: [MailingListController::class, 'unsubscribe'],
    email: $email
);
```

Alternatively, {b`\Tempest\Router\temporary_signed_uri()`} can be used to provide a duration after which the signed URI expires, providing an extra layer of security.

```php
use function Tempest\Router\temporary_signed_uri;

temporary_signed_uri(
    action: PasswordlessAuthenticationController::class,
    duration: Duration::minutes(10),
    userId: $userId
);
```

To ensure the validity of a signed URL, call the `hasValidSignature` method on the {b`Tempest\Router\UriGenerator`} class.

```php
final class PasswordlessAuthenticationController
{
    public function __construct(
        private readonly UriGenerator $uri,
    ) {}

    public function __invoke(Request $request): Response
    {
        if (! $this->uri->hasValidSignature($request)) {
            throw new HttpRequestFailed(Status::UNPROCESSABLE_CONTENT);
        }

        // …
    }
}
```

### Matching the current URI

To determine whether the current request matches a specific controller action, Tempest provides the {b`\Tempest\Router\is_current_uri()`} function. This function accepts the same arguments as `uri`, and returns a boolean.

```php "GET /aircraft/1"
use function Tempest\Router\is_current_uri;

// Providing no argument to the right controller action will match
is_current_uri(AircraftController::class); // true

// Providing the correct arguments to the right controller action will match
is_current_uri(AircraftController::class, id: 1); // true

// Providing invalid arguments to the right controller action will not match
is_current_uri(AircraftController::class, id: 2); // false
```

## Accessing request data

Web applications need to process user input—whether it is form submissions, search queries, API payloads, or filter parameters.

Tempest handles this by injecting {b`Tempest\Http\Request`} objects into controller actions, giving access to the request's body, query parameters, method, and headers through dedicated class properties.

### Using request classes

In most situations, the data expected from a request is structured. Clients are expected to send specific values and follow specific rules.

The idiomatic approach is to use request classes. These are classes with public properties that correspond to the data to retrieve from the request. Tempest automatically validates these properties using PHP's type system, in addition to optional [validation attributes](../2-features/03-validation) when needed.

A request class must implement {b`Tempest\Http\Request`} and use the {b`Tempest\Http\IsRequest`} trait, which provides the default implementation.

:::code-group

```php app/RegisterAirportRequest.php
use Tempest\Http\Request;
use Tempest\Http\IsRequest;
use Tempest\Validation\Rules\HasLength;

final class RegisterAirportRequest implements Request
{
    use IsRequest;

    #[HasLength(min: 10, max: 120)]
    public string $name;

    #[HasLength(min: 2)]
    public string $servedCity;

    #[HasLength(min: 4, max: 4)]
    public string $icaoCode;

    public ?DateTime $registeredAt = null;
}
```

```php app/AirportController.php
use Tempest\Router\Post;
use Tempest\Http\Responses\Redirect;

use function Tempest\Mapper\map;
use function Tempest\Router\uri;

final readonly class AirportController
{
    #[Post(uri: '/airports/register')]
    public function store(RegisterAirportRequest $request): Redirect
    {
        $airport = map($request)
            ->to(Airport::class)
            ->save();

        return new Redirect(uri([self::class, 'show'], id: $airport->id));
    }
}
```

```php app/Airport.php
#[Table('airports')]
final class Airport
{
    public string $name;
    public string $servedCity;
    public string $icaoCode;
    public ?DateTime $registeredAt = null;
}
```

:::

Once a request class is created, it can be injected into a controller action. Tempest fills its properties and validates them, providing a properly-typed object.

:::info A note on data mapping
The `map()` function allows mapping any data from any source into objects of your choice. You may read more about them in [their documentation](../2-features/01-mapper.md).
:::

### Sensitive fields

When a validation error occurs, Tempest filters out sensitive fields from the original values stored in the session. This prevents sensitive data from being re-populated in forms after a redirect.

Request properties can be marked as sensitive using the {b`#[Tempest\Http\SensitiveField]`} attribute:

```php app/ResetPasswordRequest.php
use Tempest\Http\Request;
use Tempest\Http\IsRequest;
use Tempest\Http\SensitiveField;
use Tempest\Validation\Rules\HasLength;

final class ResetPasswordRequest implements Request
{
    use IsRequest;

    public string $email;

    #[SensitiveField]
    #[HasLength(min: 8)]
    public string $password;
}
```

### Retrieving data directly

For simpler use cases, a value can be retrieved from the body or the query parameter using the {b`Tempest\Http\Request`}'s `get` method. Other methods, such as `hasBody` or `hasQuery`, are also available.

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

When users submit forms—like updating profile settings, or posting comments—the data needs validation before processing. Tempest automatically validates request objects using type hints and validation attributes, then provides errors back to users when something is wrong.

On validation failure, Tempest either redirects back to the form (for web pages) or returns a 422 response (for stateless requests). Validation errors are available in two places:

- As a JSON encoded string in the `{txt}X-Validation` header
- Through the {b`Tempest\Http\Session\FormSession`} class

For web pages, Tempest also provides built-in view components to display errors when they occur.

```html
<x-form :action="uri(StorePostController::class)">
  <x-input name="name" />
  <x-input type="email" name="email" />
  <x-submit />
</x-form>
```

`{html}<x-form>` is a view component that defaults to sending `POST` requests. `{html}<x-input>` is a view component that renders a label, input field, and validation errors all at once.

:::info
These built-in view components can be customized. Run `./tempest install view-components` and select the components to pull into the project. [Read more about installing view components here](../1-essentials/02-views.md#built-in-components).
:::

## Route middleware

Middleware can be applied to handle tasks between receiving a request and sending a response. To specify middleware for a route, add it to the `middleware` argument of a route attribute.

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

The middleware class must be an invokable class that implements the {b`Tempest\Router\HttpMiddleware`} interface. This interface has an `{:hl-property:__invoke:}()` method that accepts the current request as its first parameter and {b`Tempest\Router\HttpMiddlewareCallable`} as its second parameter.

{b`Tempest\Router\HttpMiddlewareCallable`} is an invokable class that forwards the `$request` to its next step in the pipeline.

```php app/ValidateWebhook.php
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

All middleware classes are sorted based on their priority. By default, each middleware has the "normal" priority, which can be overridden using the {b`#[Tempest\Core\Priority]`} attribute:

```php
use Tempest\Core\Priority;

#[Priority(Priority::HIGH)]
final readonly class ValidateWebhook implements HttpMiddleware
{ /* … */ }
```

Priority is defined using an integer. However, for consistency reasons, it is recommended to use of the built-in {b`Tempest\Core\Priority`} constants.

### Middleware discovery

Global middleware classes are discovered and sorted based on their priority. A middleware class can be made non-global by annotating it with the {b`#[Tempest\Discovery\SkipDiscovery]`} attribute:

```php
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class ValidateWebhook implements HttpMiddleware
{ /* … */ }
```

### Cross-site request forgery protection

Tempest provides [cross-site request forgery](https://en.wikipedia.org/wiki/Cross-site_request_forgery) protection based on the presence and values of the [`{txt}Sec-Fetch-Site`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Site) and [`{txt}Sec-Fetch-Mode`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Mode) headers through the {b`Tempest\Router\PreventCrossSiteRequestsMiddleware`} middleware, included by default in all requests.

Unlike traditional CSRF tokens, this approach uses browser-generated headers that cannot be forged by external websites:

- [`{txt}Sec-Fetch-Site`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Site) indicates whether the request came from the same domain, subdomain, a different site or if it was user-initiated, such as typing the URL directly,
- [`{txt}Sec-Fetch-Mode`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Mode) allows distinguishing between requests originating from a user navigating between HTML pages, and requests to load images and other resources.

:::info
This middleware requires browsers that support `{txt}Sec-Fetch-*` headers, which is the case for all modern browsers. You may [exclude this middleware](#excluding-route-middleware) and implement traditional CSRF protection using tokens if you need to support older browsers.
:::

### Excluding route middleware

Some routes do not require specific global middleware to be applied. For instance, a publicly accessible health check endpoint could bypass rate limiting that's applied to other routes. Specific middleware can be skipped by using the `without` argument of the route attribute.

```php app/HealthCheckController.php
use Tempest\Router\Get;
use Tempest\Http\Response;

final readonly class HealthCheckController
{
    #[Get('/health', without: [RateLimitMiddleware::class])]
    public function __invoke(): Response
    {
        return new Ok(['status' => 'healthy']);
    }
}
```

## Route decorators

When building an API or an administration panel, routes often share common configuration—like a URL prefix (`/api`), authentication middleware, or stateless behavior. Route decorators are attributes that can be annotated to controller classes or methods to apply common configuration.

```php app/Books/ApiController.php
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

### Built-in route decorators

Tempest includes several route decorators to handle common scenarios—like providing routes without session overhead, organizing routes under a common prefix, or applying authentication across an entire controller.

These decorators save you from creating custom implementations for frequently-needed patterns.

#### `#[Stateless]`

For API endpoints, RSS feeds, or any other kind of page that does not require cookie or session data, use the {b`#[Tempest\Router\Stateless]`} attribute to remove all state-related logic:

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

Adds a prefix to the URI for all associated routes.

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

#[WithMiddleware(AuthMiddleware::class, AdminMiddleware::class)]
final readonly class AdminController { /* … */ }
```

#### `#[WithoutMiddleware]`

Explicitly removes middleware to all associated routes.

```php
use Tempest\Router\WithoutMiddleware;
use Tempest\Router\Get;
use Tempest\Router\PreventCrossSiteRequestsMiddleware;

#[WithoutMiddleware(PreventCrossSiteRequestsMiddleware::class)]
final readonly class StatelessController { /* … */ }
```

### Custom route decorators

Custom route decorators are built by implementing the {b`\Tempest\Router\RouteDecorator`} interface and marking the decorator as an attribute. The `decorate()` method receives the current {b`Tempest\Router\Route`} as a parameter, and must return the modified route.

```php
use Attribute;
use Tempest\Router\RouteDecorator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class Auth implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        $route->middleware[] = AuthMiddleware::class;

        return $route;
    }
}
```

## Responses

All requests to a controller action expect a response to be returned to the client. This is done by returning a {b`Tempest\View\View`} or a {b`Tempest\Http\Response`} object.

For simpler use cases or debugging purposes, scalar values and arrays can also be returned directly. Tempest automatically converts these values into proper responses.

### View responses

Returning a view is a shorthand for returning a successful response with that view. The {b`Tempest\view()`} function can be used directly to construct a view.

```php app/Aircraft/AircraftController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

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

Tempest has a templating system inspired by modern front-end frameworks like [Vue](https://vuejs.org). Read more about views in the [dedicated chapter](./02-views.md).

### Using built-in response classes

Tempest provides several response classes for common use cases, all implementing the {b`Tempest\Http\Response`} interface, mostly named after HTTP statuses.

- {b`Tempest\Http\Responses\Ok`} — the 200 response. Accepts an optional body.
- {b`Tempest\Http\Responses\Created`} — the 201 response. Accepts an optional body.
- {b`Tempest\Http\Responses\Redirect`} — redirects to the specified URI.
- {b`Tempest\Http\Responses\Back`} — redirects to previous page, accepts a fallback.
- {b`Tempest\Http\Responses\Download`} — downloads a file from the browser.
- {b`Tempest\Http\Responses\File`} — shows a file in the browser.
- {b`Tempest\Http\Responses\NotFound`} — the 404 response. Accepts an optional body.
- {b`Tempest\Http\Responses\ServerError`} — a 500 server error response.

The following example conditionally returns a {b`Tempest\Http\Responses\Redirect`}, otherwise letting the user download a file by sending a {b`Tempest\Http\Responses\Download`} response:

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
        if (! $this->accessControl->isGranted('view', $flight)) {
            return new Redirect('/');
        }

        return new Download($flight->flight_plan_path);
    }
}
```

### Sending generic responses

When the response's status code needs to be dynamically computed without using a condition to send the corresponding response object, return an instance of {b`Tempest\Http\GenericResponse`} and specify the status code and an optional body.

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

There are situations where the same kind of response is sent in multiple places, or where a proper API is needed for sending a structured response.

Custom response classes can be created by implementing {b`Tempest\Http\Response`}, which default implementation is provided by the {b`Tempest\Http\IsResponse`} trait:

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

Tempest automatically infers the response's content type, typically from the request's `{txt}Accept` header.

However, the content type can be overridden manually by using the `setContentType` method on {b`Tempest\Http\Response`} classes. This method accepts a case of {b`Tempest\Http\ContentType`}.

```php app/JsonController.php
use Tempest\Router\Get;
use Tempest\Http\ContentType;
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

There are situations where actions need to be taken on a response right before it is sent to the client. For instance, custom error pages can be displayed when an exception occurred, or a redirect can be performed instead of displaying the [built-in HTTP 404](/hello-from-the-void){:ssg-ignore="true"} page.

This can be done using a response processor. Similar to [view processors](./02-views.md#pre-processing-views), these are classes that implement the {b`Tempest\Router\ResponseProcessor`} interface. In the `process()` method, the response object can be mutated and returned:

```php app/ErrorResponseProcessor.php
use function Tempest\View\view;

final readonly class ErrorResponseProcessor implements ResponseProcessor
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

## Session management

Sessions in Tempest are managed by the {b`Tempest\Http\Session\Session`} class. It can be injected anywhere needed. As soon as the {b`Tempest\Http\Session\Session`} is injected, it is started behind the scenes.

```php
use Tempest\Http\Session\Session;

final readonly class TodoController
{
    public function __construct(
        private Session $session,
    ) {}

    #[Post('/select/{todo}')]
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

After saving data or performing an action, it is often needed to show users a success message, error notification, or status update that appears once and then disappears after they refresh the page.

Use the `flash()` method on the {b`Tempest\Http\Session\Session`} to store a value that lasts for the next request only:

```php
public function store(Todo $todo): Redirect
{
    $this->session->flash('message', value: 'Save was successful');
    
    return new Redirect('/');
}
```

### Session configuration

Tempest supports file and database-based sessions, the former being the default option. Sessions can be configured by creating a `session.config.php` file, in which the expiration time and the session driver can be specified.

#### File sessions

When using file-based sessions, which is the default, session data is stored in files within the specified directory, relative to `.tempest`. The path and expiration duration can be configured as follows:

```php app/session.config.php
use Tempest\Http\Session\Config\FileSessionConfig;
use Tempest\DateTime\Duration;

return new FileSessionConfig(
   expiration: Duration::days(30),
   path: 'sessions',
);
```

#### Database sessions

Tempest provides a database-based session driver, particularly useful for applications that run on multiple servers, as session data can be shared across all instances.

Before using database sessions, a dedicated table is needed. Tempest provides a migration that can be installed using its installer:

```sh
./tempest install sessions:database
```

This installer also suggests creating the configuration file that sets up database sessions, with a default expiration of 30 days:

```php app/Sessions/session.config.php
use Tempest\Http\Session\Config\DatabaseSessionConfig;
use Tempest\DateTime\Duration;

return new DatabaseSessionConfig(
    expiration: Duration::days(30),
);
```

### Session cleaning

Sessions expire based on the last activity time. This means that as long as a user is actively using the application, their session remains valid.

Outdated sessions must occasionally be cleaned up. Tempest provides a built-in command to do so, `session:clean`. This command uses the [scheduler](../2-features/11-scheduling.md): with scheduling enabled, it automatically runs behind the scenes.

## Deferring tasks

During requests, tasks that take a few seconds to complete are sometimes needed. This could be sending an email or keeping track of a page visit.

Tempest provides a way to perform that task after the response has been sent, so the client does not have to wait until its completion. This is done by passing a callback to the `defer` function:

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

The `defer` callback can accept any parameter that the container can inject.

:::warning
Task deferring only works if [`fastcgi_finish_request()`](https://www.php.net/manual/en/function.fastcgi-finish-request.php) is available within your PHP installation. If it's not available, deferred tasks will still be run, but the client response will only complete after all tasks have been finished.
:::

## Testing

Tempest provides a router testing utility accessible through the `http` property of the [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php) test case. Learn more about testing in the [dedicated chapter](./07-testing.md).

The router testing utility provides methods for all HTTP verbs. These methods return an instance of [`TestResponseHelper`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/Http/TestResponseHelper.php), giving access to multiple assertion methods.

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
