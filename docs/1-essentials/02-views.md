---
title: Views
description: "Tempest provides a modern templating engine with syntax inspired by the best front-end frameworks. However, Blade, Twig or any other engine can be used if you prefer so."
keywords: "Experimental"
---

## Overview

Views in Tempest are parsed by Tempest View, our own templating engine. Tempest View uses a syntax that can be thought of as a superset of HTML. If you prefer using a templating engine with more widespread support, [you may also use Blade, Twig, or any other](#using-other-engines) — as long as you provide a way to initialize it.

If you'd like to Tempest View as a standalone component in your project, you can read the documentation on how to do so [here](../5-extra-topics/02-standalone-components.md#tempest-view).

### Syntax overview

The following is an example of a view that inherits the `x-base` component, passing a `title` property.

Inside, a `x-post` [component](#view-components) is rendered multiple times thanks to a [foreach loop](#foreach-and-forelse) on `$this->posts`. That component has a default [slot](#using-slots), in which the post details are rendered. The [control flow](#control-flow-directives) is implemented using HTML attributes that start with colons `:`.

```html
<x-base title="Home">
    <x-post :foreach="$this->posts as $post">
        {{-- a comment which won't be rendered to HTML --}}
        
        {!! $post->title !!}

        <span :if="$this->showDate($post)">
            {{ $post->date }}
        </span>
        <span :else>
            -
        </span>
    </x-post>
    <div :forelse>
        <p>It's quite empty here…</p>
    </div>

    <x-footer />
</x-base>
```

## Rendering views

As specified in the documentation about [sending responses](./01-routing.md#view-responses), views may be returned from controller actions using the `{php}view` function. This function is a shorthand for instantiating a {`Tempest\View\View`} object.

```php app/AircraftController.php
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class AircraftController
{
    #[Get(uri: '/aircraft/{aircraft}')]
    public function show(Aircraft $aircraft): View
    {
        return view('aircraft.view.php', aircraft: $aircraft);
    }
}
```

### View paths

The `view` function accepts the path to a view as its first parameter. This path may be relative or absolute, depending on your preference.

The following three examples are equivalent:

```php
return view(__DIR__ . '/views/home.view.php');
return view('./views/home.view.php');
return view('views/home.view.php');
```

### Using dedicated view objects

A view object is a dedicated class that represent a specific view.

Using view objects will improve static insights in your controllers and view files, and may offer more flexibility regarding how the data may be constructed before being passed on to a view file.

```php
final class AircraftController
{
    #[Get('/aircraft/{type}/{aircraft}')]
    public function show(AircraftType $type, Aircraft $aircraft): AircraftView
    {
        return new AircraftView($aircraft, $type);
    }
}
```

To create a view object, implement the {`Tempest\View\View`} interface, and add the {`Tempest\View\IsView`} trait, which provides the default implementation.

```php app/AircraftView.php
use Tempest\View\View;
use Tempest\View\IsView;

use function Tempest\root_path;

final class AircraftView implements View
{
    use IsView;

    public function __construct(
        public Aircraft $aircraft,
        public AircraftType $type,
    ) {
        $this->path = root_path('src/Aircraft/aircraft.view.php');
    }
}
```

In a view file rendered by a view object, you may add a type annotation for `$this`. This allows IDEs like [PhpStorm](https://www.jetbrains.com/phpstorm/) to infer variables and methods.

```html app/Aircraft/aircraft.view.php
<?php /** @var \App\Modules\Home\HomeView $this */ ?>

<p :if="$this->type === AircraftType::PC24">
	The {{ $this->aircraft->icao_code }} is a light business jet
	produced by Pilatus Aircraft of Switzerland.
</p>
```

View objects are an excellent way of encapsulating view-related logic and complexity, moving it away from controllers, while simultaneously improving static insights.

## Templating syntax

### Text interpolation

Text interpolation is done using the "mustache" syntax. This will escape the given variable or PHP expression before rendering it.

```html
<span>Welcome, {{ $username }}</span>
```

To avoid escaping the data, you may use the following syntax. This should only be used on trusted, sanitized data, as this can open the door to an [XSS vulnerability](https://en.wikipedia.org/wiki/Cross-site_scripting):

```html
<div>
	{!! $content !!}
</div>
```

### Expression attributes

Expression attributes are HTML attributes that are evaluated as PHP code. Their syntax is the same as HTML attributes, except they are identified by a colon `:`:

```html
<html :lang="$this->user->language"></h1>
<!-- <html lang="en"></h1> -->
```

As with text interpolation, only variables and PHP expressions that return a value are allowed. Mustache and PHP opening tags cannot be used inside them:

```html
<!-- This is invalid -->
<h1 :title="<?= $this->post->title ?>"></h1>
```

When using expression attributes on normal HTML elements, only [scalar](https://www.php.net/manual/en/language.types.type-system.php#language.types.type-system.atomic.scalar) and `Stringable` values can be returned. However, any object can be passed down to a [component](#view-components).

### Boolean attributes

The HTML specification describes a special kind of attributes called [boolean attributes](https://html.spec.whatwg.org/multipage/common-microsyntaxes.html#boolean-attribute). These attributes don't have a value, but indicate `true` whenever they are present.

Using an expression attribute that return a boolean variable will follow the HTML specification, effectively not rendering the attribute if the value is `false`.

```html
<option :value="$value" :selected="$selected">{{ $label }}</option>
```

Depending on whether `$selected` evaluates to `true` or `false`, the above example may or may not render the `selected` attribute.

Apart from HTMLs boolean attributes, the same syntax can be used with any expression attribute as well:

```html
<div :data-active="{$isActive}"></div>

<!-- <div></div> when $isActive is falsy -->
<!-- <div data-active></div> when $isActive is truthy -->
```

### Control flow directives

#### `:if`, `:elseif`, and `:else`

The `:if` directive can conditionally render the element it is attached to, depending on the result of its expression. Similarly, the `:elseif` and `:else` directives can be used on direct siblings for additional control.

```html
<span :if="$this->pendingUploads->isEmpty()">Import files</span>
<span :else>Import {{ $this->pendingUploads->count() }} file(s)</span>
```

#### `:isset`

The `:isset` directive can be used to conditionally render the element it is attached to, depending on the existence of a variable.

```html
<h1 :isset="$title">{{ $title }}</h1>
```

Since `:isset` is a shorthand for `:if="isset()"`, it can be combined with `:elseif` and `:else`:

```html
<h1 :isset="$title">{{ $title }}</h1>
<h1 :else>Title</h1>
```

#### `:foreach` and `:{:hl-keyword:forelse:}`

The `:foreach` directive may be used to render the associated element multiple times based on the result of its expression. Combined with `:{:hl-keyword:forelse:}`, an empty state can be displayed when the data is empty.

```html
<li :foreach="$this->reports as $report">
  {{ $report->title }}
</li>
<li :forelse>
	There is no report.
</li>
```

### Templates

The built-in `{html}<x-template>` element may be used as a placeholder when you want to use a directive without rendering an actual element in the DOM.

```html
<x-template :foreach="$posts as $post">
    <div>{{ $post->title }}</div>
</x-template>
```

The example above will only render the child `div` elements:

```html
<div>Post A</div>
<div>Post B</div>
<div>Post C</div>
```

## View components

Components allow for splitting the user interface into independent and reusable pieces.

Tempest doesn't have a concept of extending other views. Instead, a component may include another component using the same syntax as other HTML elements.

### Registering view components

To create a view component, create a `.view.php` file that starts with `x-`. These files are referred to as anonymous view components and are automatically discovered by Tempest.

```html app/x-base.view.php
<html lang="en">
	<head>
		<title :if="$title ?? null">{{ $title }} — AirAcme</title>
		<title :else>AirAcme</title>
	</head>
	<body>
		<x-slot />
	</body>
</html>
```

### Using view components

All views may include a view component. In order to do so, you may simply use a component's name as a tag, including the `x-` prefix:

```html app/home.view.php
<x-base :title="$this->post->title">
	<article>
		{{ $this->post->body }}
	</article>
</x-base>
```

The example above demonstrates how to pass data to a component using an [expression attribute](#expression-attributes), as well as how to pass elements as children if that component where the `<x-slot />` tag is used.

### Attributes in components

Attributes and [expression attributes](#expression-attributes) may be passed into view components. They work the same way as normal elements, and their values will be available in variables of the same name:

```html home.view.php
<x-base :title="$this->post->title">
	// ...
</x-base>
```

```html x-base.view.php
// ...
<title :if="$title ?? null">{{ $title }}</title>
```

Note that the casing of attributes will affect the associated variable name:

- `{txt}camelCase` and `{txt}PascalCase` attributes will be converted to `$lowercase` variables
- `{txt}kebab-case` and `{txt}snake_case` attributes will be converted to `$camelCase` variables.

:::info
The idiomatic way of using attributes is to always use `{txt}kebab-case`.
:::

### Fallthrough attributes

When `{html}class` and `{html}style` attributes are used on a view component, they will automatically be added to the root node, or merged with the existing attribute if it already exists.

```html x-button.view.php
<button class="rounded-md px-2.5 py-1.5 text-sm">
	<!-- ... -->
</button>
```

The example above defines a button component with a default set of classes. Using this component and providing another set of classes will merge them together:

```html index.view.php
<x-button class="text-gray-100 bg-gray-900" />
```

Similarly, the `id` attribute will always replace an existing `id` attribute on the root node of a view component.

### Dynamic attributes

An `$attributes` variable is accessible within view components. This variable is an array that contains all attributes passed to the component, except expression attributes.

Note that attribute names use `{txt}kebab-case`.

```html x-badge.view.php
<span class="px-2 py-1 rounded-md text-sm bg-gray-100 text-gray-900">
	{{ $attributes['value'] }}
</span>
```

### Using slots

The content of components is often dynamic, depending on external context to be rendered. View components may define zero or more slot outlets, which may be used to render the given HTML fragments.

```html x-button.view.php
<button class="rounded-md px-2.5 py-1.5 text-sm text-gray-100 bg-gray-900">
	<x-slot />
</button>
```

The example above defines a button component with default classes, and a slot inside. This component may be used like a normal HTML element, providing the content that will be rendered in the slot outlet:

```html index.view.php
<x-button>
	<!-- This will be injected into the <x-slot /> outlet -->
	<x-icon name="tabler:x" />
	<span>Delete</span>
</x-button>
```

### Default slot content

A view component's slot can define a default value, which will be used when a view using that component doesn't pass any value to it:

```html x-my-component.view.php
<div>
    <x-slot>Fallback value</x-slot>
    <x-slot name="a">Fallback value for named slot</x-slot>
</div>
```

```html
<x-my-component />

<!-- Will render "Fallback value" and "Fallback value for named slot" -->
```

### Named slots

When a single slot is not enough, names can be attached to them. When using a component with a named slot, you may use the `<x-slot>` tag with a `name` attribute to render content in a named outlet:

```html x-base.view.php
<html lang="en">
	<head>
		<!-- … -->
		<x-slot name="styles" />
	</head>
	<body>
		<x-slot />
	</body>
</html>
```

The above example uses a slot named `styles` in its `<head>` element. The `<body>` element has a default, unnamed slot. A view component may use `<x-base>` and optionally refer to the `styles` slot using the syntax mentioned above, or simply provide content that will be injected in the default slot:

```html index.view.php
<x-base title="Hello World">
	<!-- This part will be injected into the "styles" slot -->
	<x-slot name="styles">
		<style>
			body {
				/* … */
			}
		</style>
	</x-slot>

	<!-- Everything not living in a slot will be injected into the default slot -->
	<p>
		Hello World
	</p>
</x-base>
```

### Dynamic slots

Within a view component, a `$slots` variable will always be provided, allowing you to dynamically access the named slots within the component.

This variable is an instance of {`Tempest\View\Slot`}, with has a handful of properties:

- `{php}$slot->name`: the slot's name
- `{php}$slot->content`: the compiled content of the slot
- `{php}$slot->attributes`: all the attributes defined on the slot
- `{php}$slot->{attribute}`: dynamically access an attribute defined on the slot

For instance, the snippet below implements a tab component that accepts any number of tabs.

```html x-tabs.view.php
<div :foreach="$slots as $slot">
	<h1 :title="$slot->title">{{ $slot->name }}</h1>
	<p>{!! $slot->content !!}</p>
</div>
```

```html
<x-tabs>
	<x-slot name="php" title="PHP">This is the PHP tab</x-slot>
	<x-slot name="js" title="JavaScript">This is the JavaScript tab</x-slot>
	<x-slot name="html" title="HTML">This is the HTML tab</x-slot>
</x-tabs>
```

### Dynamic view components

On some occasions, you might want to dynamically render view components, for example, render a view component whose name is determined at runtime. You can use the `{html}<x-component :is="">` element to do so:

```html
<!-- $name = 'x-post' -->

<x-component :is="$name" :title="$title" />
```

### View component scope

View components act almost exactly the same as PHP's closures: they only have access to the variables you explicitly provide them, and any variable defined within a view component won't leak into the out scope.

The only difference with normal closures is that view components also have access to view-defined variables as local variables.

```html
<?php 
$title = 'foo';
?>

<!-- $title will need to be passed in explicitly, 
     otherwise `x-post` wouldn't know about it: -->

<x-post :title="$title"></x-post>
```

```php
/* View-defined data will be available within the component directly */
final class HomeController
{
    #[Get('/')]
    public function __invoke(): View
    {
        return view('<x-base />', siteTitle: 'Tempest');
    }
}
```

```html x-base.view.php
<h1>{{ $siteTitle }}</h1>
```

## Built-in components

Besides components that you may create yourself, Tempest provides a default set of useful built-in components to improve your developer experience. Any vendor-provided component can be published in your own project by running the `tempest install` command:

```console
./tempest install view-components

 <dim>│</dim> <em>Select which view components you want to install</em>
 <dim>│</dim> / <dim>Filter...</dim>
 <dim>│</dim> → ⋅ x-csrf-token
 <dim>│</dim>   ⋅ x-markdown
 <dim>│</dim>   ⋅ x-input
 <dim>│</dim>   ⋅ x-icon
 
<comment>…</comment>
```

Any component with the same name that lives in your local project will get precedence over vendor-defined components.

### `x-base`

A base template you can install into your own project as a starting point. This one includes the Tailwind CDN for quick prototyping.

```html
<x-base :title="Blog">
    <h1>Welcome!</h1>
</x-base>
```

### `x-form`

This component provides a form element that will post by default and includes the csrf token out of the box:

```html
<?php
use function \Tempest\Router\uri;
?>

<x-form :action="uri(StorePostController::class)">
    <!-- … -->
</x-form>
```

### `x-input`

A versatile input component that will render labels and validation errors automatically.

```html
<x-input name="title" />
<x-input name="content" type="textarea" label="Write your content" />
<x-input name="email" type="email" id="other_email" />
```

### `x-submit`

A submit button component that prefills with a "Submit" label:

```html
<x-submit />
<x-submit label="Send" />
```

### `x-csrf-token`

Includes the CSRF token in a form

```html
<form action="…">
    <x-csrf-token />
</form>
```

### `x-icon`

This component provides the ability to inject any icon from the [Iconify](https://iconify.design/) project in your templates.

```html
<x-icon name="material-symbols:php" class="size-4 text-indigo-400" />
```

The first time a specific icon is being rendered, Tempest will query the [Iconify API](https://iconify.design/docs/api/queries.html) to fetch the corresponding SVG tag. The result of this query will be cached indefinitely, so it can be reused at no further cost.

:::info
Iconify has a large collection of icon sets, which you may browse using the [Icônes](https://icones.js.org/) directory.
:::

### `x-vite-tags`

Tempest has built-in support for [Vite](https://vite.dev/), the most popular front-end development server and build tool. You may read more about [asset bundling](../2-features/05-asset-bundling.md) in the dedicated documentation.

This component simply injects registered entrypoints where it is called.

```html x-base.view.php
<html lang="en">
	<head>
		<x-vite-tags />
	</head>
	<!-- ... -->
</html>
```

Optionally, it accepts an `entrypoint` attribute. If it is passed, the component will not inject other entrypoints discovered by Tempest.

```html x-base.view.php
<x-vite-tags entrypoint="src/main.ts" />
```

### `x-markdown`

The `{html}x-markdown` component can be used to render markdown content, either directly from your view files, or by passing a content variables into it:

```html
<x-markdown># hi</x-markdown>
<x-markdown :content="$text" />
```

## Pre-processing views

In most applications, some views will need access to common data. To avoid having to manually provide this data to views through controller methods, it is possible to use view processors to manipulate views before they are rendered.

To create a view processor, create a class that implements the {`Tempest\View\ViewProcessor`} interface. It requires a `process()` method in which you may mutate and return the view that will be rendered.

```php
use Tempest\View\View;
use Tempest\View\ViewProcessor;

final class StarCountViewProcessor implements ViewProcessor
{
    public function __construct(
        private readonly GitHub $github,
    ) {}

    public function process(View $view): View
    {
        if (! $view instanceof WithStargazersCount) {
            return $view;
        }

        return $view->data(stargazers: $this->github->getStarCount());
    }
}
```

The example above provides the `$stargazers` variable to all view classes that implement the `WithStargazersCount` interface.

## View caching

Tempest views are always compiled to plain PHP code before being rendered. During development, this is done on-the-fly, every time. In production, these compiled views should be cached to avoid the performance overhead. This is done by setting the `{txt}{:hl-property:VIEW_CACHE:}` environment variable:

```env .env
{:hl-property:VIEW_CACHE:}={:hl-keyword:true:}
```

During deployments, that cache must be cleared in order to not serve outdated views to users. You may do that by running `tempest view:clear` on every deploy.

## Tempest View as a standalone engine

Tempest View is also designed to be used as a standalone engine in whatever PHP project you want. Start by requiring `tempest/view`:

```sh
composer require tempest/view
```

As a bare minimum setup, you can create an instance of the renderer by calling `TempestViewRenderer::make()`:

```php
use Tempest\View\Renderers\TempestViewRenderer;
use function Tempest\View\view;

$renderer = TempestViewRenderer::make();

$html = $renderer->render(view('home.view.php', name: 'Brent'));
```

If, however, you want view component support, you will need to provide a `ViewConfig` object as well:

```php
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewConfig;

$config = new ViewConfig()->addViewComponents(
    __DIR__ . '/components/x-base.view.php',
    __DIR__ . '/components/x-footer.view.php',
    __DIR__ . '/components/x-header.view.php',
);

$renderer = TempestViewRenderer::make($config);
```

If you want to rely on Tempest's discovery to find view components, you can boot a minimal version of Tempest, and resolve the view renderer from the container:

```php
use Tempest\Core\Tempest;
use Tempest\View\ViewRenderer;

$container = Tempest::boot(__DIR__);

$html = $container->get(ViewRenderer::class)->render(
    view('home.view.php', name: 'Brent')
);
```

You can choose whichever way you prefer. Chances are that, if you use the minimal setup without booting Tempest, you'll want to add a custom view component loader. That's up to you to implement then.

### A note on caching

When you're using the minimal setup, view caching can be enabled by passing in a `$viewCache` parameter into `TempestViewRenderer::make()`:

```php
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewCache;

$renderer = TempestViewRenderer::make(
    viewCache: ViewCache::create(),
);
```

It's recommended to turn view caching on in production environments. To clear the view cache, you can call the `clear()` method on the `ViewCache` object:

```php
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewCache;

$viewCache = ViewCache::create();
$viewCache->clear();

$renderer = TempestViewRenderer::make(
    viewCache: $viewCache,
);
```

## Separate view directories

View files can live in any directory that is discoverable by Tempest. That means: a directory with a PSR-4 namespace associated with it. If you want your view files to live outside of `src` or `app`, you can add a namespace for it in composer.json:

```json composer.json
"autoload": {
    "psr-4": {
        "App\\": "src/",
        "Views\\": "views/"
    },
}
```

Don't forget to run `composer up` after making changes to your composer.json file.

Note that view files themselves don't need a namespace; this namespace is only here to tell Tempest that `views/` is a directory it should scan. If you want to add a class in the `Views` namespace (like, for example, a [custom view object](/2.x/essentials/views#using-dedicated-view-objects)), then that is possible as well.

## Using other engines

While Tempest View is simple to use, it currently lacks tooling support from editors and IDEs. You may also simply prefer other templating engines. For these reasons, you may use any other engine of your choice.

Out-of-the-box, Tempest has support for Twig and Blade. Note that the view loaders for other engines are not based on Tempest's discovery, so the syntax to refer to a specific view might differ.

### Using Twig

You will first need to install the Twig engine. It is provided by the `twig/twig` package:

```sh
composer require twig/twig
```

The next step is to provide the configuration needed for Twig to find your view files.

```php app/twig.config.php
return new TwigConfig(
    viewPaths: [
        __DIR__ . '/views/',
    ],
);
```

Finally, update the view configuration to use the Twig renderer:

```php view.config.php
return new ViewConfig(
    rendererClass: \Tempest\View\Renderers\TwigViewRenderer::class,
);
```

### Using Blade

You will first need to install the Blade engine. Tempest provides a bridge distributed as `tempest/blade`:

```
composer require tempest/blade
```

The next step is to provide the configuration needed for Blade to find your view files.

```php blade.config.php
return new BladeConfig(
    viewPaths: [
        __DIR__ . '/views/',
    ],
);
```

Finally, update the view configuration to use the Blade renderer:

```php view.config.php
return new ViewConfig(
    rendererClass: \Tempest\View\Renderers\BladeViewRenderer::class,
);
```

### Using something else

Tempest refers to the view configuration to determine which view renderer should be used. By default, it uses Tempest View's renderer, {`Tempest\View\Renderers\TempestViewRenderer`}. When using Blade or Twig, we provided {`Tempest\View\Renderers\BladeViewRenderer`} or {`Tempest\View\Renderers\TwigViewRenderer`}, respectively.

#### Implementing your own renderer

If you prefer using another templating engine, you will need to write your own renderer by implementing the {`Tempest\View\ViewRenderer`} interface.

This interface only requires a `render` method. It will be responsible for taking a {`Tempest\View\View`} instance and rendering it to a PHP file.

As an example, the Blade renderer is as simple as the following:

```php
use Tempest\Blade\Blade;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

final readonly class BladeViewRenderer implements ViewRenderer
{
    public function __construct(
        private Blade $blade,
    ) {
    }

    public function render(View|string|null $view): string
    {
        return $this->blade->render($view->path, $view->data);
    }
}
```

Once your renderer is implemented, you will need to configure Tempest to use it. This is done by creating or updating a `ViewConfig`:

```php view.config.php
return new ViewConfig(
    rendererClass: YourOwnViewRenderer::class,
);
```

#### Initializing your engine

The renderer will be called every time a view is rendered. If your engine has an initialization step, it may be a good idea to use a singleton [initializer](../1-essentials/05-container.md#dependency-initializers) to construct it.

As an example, here is a simplified version of the initializer that creates the `Blade` object, used by the Blade renderer:

```php
use Tempest\Blade\Blade;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final readonly class BladeInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        if (! class_exists(Blade::class)) {
            return false;
        }

        return $class->getName() === Blade::class;
    }

    #[Singleton]
    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): object
    {
        $bladeConfig = $container->get(BladeConfig::class);

        return new Blade(
            viewPaths: $bladeConfig->viewPaths,
            cachePath: Tempest\internal_storage_path($bladeConfig->cachePath ?? 'cache/blade'),
        );
    }
}
```
