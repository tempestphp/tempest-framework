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

The `:isset` directive will also detect when you have multiple cases, and will wrap each variable with `isset()` for you. Consider this example:

```html
<h1 :isset="$foo || $bar">Welcome!</h1>
```

If either `isset($foo)` or `isset($bar)` returns `true`, then the condition is met, and the element will be conditionally rendered.

You can also use `!isset($foo)` for inverse if needed.

Note: Ensuring that the expression returns `true` or `false` and thus applies the condition correctly is left down to you, the directive will simply wrap each `$var` with `isset()` preserving operators, without performing any logic checks itself. If you make an incompatible string, it will throw an Exception which you'll be able to view in the Debug log or interface, when enabled.

Since `:isset` is a shorthand for `:if="isset()"`, it can be combined with `:elseif` and `:else`:

```html
<h1 :isset="$title">{{ $title }}</h1>
<h1 :else>Title</h1>
```

#### `{:hl-property::foreach:}` and `:{:hl-property:forelse:}`

The `{:hl-property::foreach:}` directive may be used to render the associated element multiple times based on the result of its expression. Combined with `:{:hl-property:forelse:}`, an empty state can be displayed when the data is empty.

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

### Tag override with the `as` prop

The `as` attribute allows you to transform the rendered tag of one element into another. This takes place on an instance of `GenericElement`, so for example this code:

```html
<a as="button">My Link</a>
```

Would render

```html
<button>My Link</button>
```

The power behind this is when you use an `Expression` to determine the element.

Say for example, you wish to have a `<x-link>` component which renders as an `<a>` when the `$href` attribute is provided. In your view, use the component like so:

```html
<x-link href="https://tempestphp.com">Click to go to an awesome website</x-link>

<x-link>This is just a button</x-link>
```

In your `<x-link>` component, define:

```html
<a :as="$href ?? 'button'" :href="$href ?? ''"><x-slot /></a>
```

Your page will render two links, as follows:

```html
<a href="https://tempestphp.com">Click to go to an awesome website</a>

<button>This is just a button</button>
```

#### Where this can and cannot be used

You can't use the `as` Attribute on things like `<x-template>`, `<x-slot>`, etc, as these do not themselves render any HTML. They are placeholders in the page. Nor will placing it on a view component itself inherently do anything. The `as` attribute CAN be passed to a ViewComponent as shown in the example above, but by itself it will actually do nothing, unless you specifically provide logic to place it where you want it.

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

When `{html}class` and `{html}style` attributes, or `{html}id` is provided on a view component, Tempest will attempt to automatically apply these to the root node within the view component.

:::info
In previous releases (3.8.0 and prior), Tempest would attempt to *merge* these values, however there was no way to prevent this, or customise the behaviour. There was also a bug in applying the attributes, which meant that in many cases it didn't apply at all, resulting in inconsistent behaviour. This has been resolved, but has a new default behaviour, as explained below.
:::

Assume you have a `button`, like so, with a default set of classes present:

```html x-button.view.php
<button class="rounded-md px-2.5 py-1.5 text-sm">
	<x-slot />
</button>
```

Now, in your page, you may utilise the element:

```html index.view.php
<x-button id="myBtn" style="color: red;" />
```

As these attributes automatically apply, your button will be converted to this:

```html
<button id="myBtn" style="color: red;" class="rounded-md px-2.5 py-1.5 text-sm" />
```

#### Disabling automatic fallthrough

Tempest will attempt to apply `{html}class`, `{html}style`, and `{html}id` automatically, when they are passed to a view component. For example:

```html index.view.php
<x-button id="myBtn" style="color: red;" />
```

With the above, Tempest will attempt to apply `{html}style`, and `{html}id` automatically. As `{html}class` isn't configured, it isn't applied.

In the view component itself, you can configure `{html}class`, `{html}style`, and `{html}id` to anything you want, and Tempest will not overwrite them. You can of course, also then use these classes however you want to use them:

```html x-button.view.php
<button :id="uniqid(($id ?? 'mybtn') . '_')" :class="$class ?? 'rounded-md px-2.5 py-1.5 text-sm'">
	<x-slot />
</button>
```

When you use this version of `<x-button />`:

- `{html}id` will now default to `mybtn_(sequence generated by uniqid)`,
- `{html}style` will not appear automatically, as it was not supplied,
- `{html}class` will have a default, you can of course instead concatenate these strings, or use a CVA utility for smart class merging, or anything you want.

For example, pass one or more classes:

```html
<x-button id="myBtn" class="override" />
```

And you'll get

```html
<button class="override" id="myBtn_69cad27787c20"></button>
```

### Controlling fallthrough attributes with the Apply attribute

You can also leverage the `ApplyAttribute` to completely control the behaviour, and add further fallthrough attributes, if you wish. When `:apply` is detected on a view component, Tempest will disable all automatic fallthrough attributes, for that instance of the view component. If you are familiar with JS frontend frameworks, this is not dissimilar to a one-way `v-bind` in Vue, or a spread props operator in other languages.

By default, `$attributes` is an `ImmutableArray` and so we can manipulate it with the methods available on that class.

:::info
You cannot mix `ApplyAttribute` with automatic fallthrough attributes. Opting to use the `ApplyAttribute` hands you full control of which attributes are applied, which means you then need to declare these.
:::

#### Excluding specific fallthrough attributes

To exclude specific attributes from falling through, configure your `button` view component like this:

```html x-button.view.php
<button :apply="$attributes->removeKeys(['id', 'style'])">
	<x-slot />
</button>
```

:::info
The `removeKeys` method returns all key=>value pairs, except for those specified. You can also use the `filter` method if you need to use a closure to filter.
:::

Now, when utilising it in your page:

```html index.view.php
<x-button id="myBtn" style="color: red;" class="rounded-md px-2.5 py-1.5 text-sm" />
```

Will result in:

```html
<button class="rounded-md px-2.5 py-1.5 text-sm" />
```

#### Including only specific fallthrough attributes

To include only specific attributes, configure your `button` view component like this:

```html x-button.view.php
<button :apply="$attributes->removeKeysExcept(['class', 'width', 'height'])">
	<x-slot />
</button>
```

:::info
The `removeKeysExcept` method returns only the specified key=>value pairs. You can also use the `filter` method if you need to use a closure to filter.
:::

Now, when utilising it in your page:

```html index.view.php
<x-button id="myBtn" style="color: red;" class="rounded-md px-2.5 py-1.5 text-sm"  width="1em" height="1em" />
```

Tempest will apply only the specified attributes:

```html
<button class="rounded-md px-2.5 py-1.5 text-sm" />
```

#### Advanced usage of the Apply attribute

As the `ApplyAttribute` simply stringifies string and boolean values from the provided array, you can build the array however you like.

Consider this example `button`:

```html x-button.view.php
<?php
$apply = [
    'class' => $class ?? null,
    'href' => $href ?? '',
    'target' => (isset($href) && str_contains($href, 'http')) ? '_blank' : null,
];
?>

<button :as="$apply['href'] !== '' ? 'a' : 'button'" :apply="$apply">{{ $label ?? '' }}</button>
```

Now, when utilising it in your page:

```html index.view.php
<x-button href="https://tempestphp.com" label="Tempest, the framework that gets out of your way" />
```

Tempest will spread the supplied attributes, and as we also used the `AsAttribute` to convert it to a `{html}a` when `$href` is populated, you will get a hyperlink:

```html
<a href="https://www.tempestphp.com" target="_blank">Tempest, the framework that gets out of your way</a>
```

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

### Define slot ownership in nested view components

You can use `<x-slot name="mySlot" />` interchangeably to both *define* a slot with optional default content, or to provide content to *populate* the slot. Tempest will consider the hierarchy of the components from the AST to automatically detect your intent, however in more complex, especially nested, view components this can result in unexpected behaviour.

To override this behaviour and manually control in which view component your slots are considered to be *defined*, you can use `<x-slot define="mySlot" />` syntax instead. This causes the slot to be registered against the view component in which the keyword 'define' is used, instead of where the `slot` itself appears in the AST.

#### Extendable view component example using `define`

Let us assume you have an `x-container` view component, which is a `<div>` with formatting to act as a flex container for responsive sizing. You use this component repeatedly across your project, and it's effectively a macro to open and close the `<div>`; it doesn't have any slots or do anything special itself otherwise, with only a default `<x-slot/>` to render whatever it is given.

```html x-container.view.php
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><x-slot/></div>
```
Now, assume we have an `x-header` in which we wish to use the `x-container`. Our `x-header` wishes to place slots `left` and `right` inside it; `x-header` owns these slots and wishes to expose these slots at the callsite in case they need custom content. Using the `define` keyword tells Tempest to treat these slots as *defined* by `x-header` instead of as a *slot to fill* inside `x-container`. Using `name` here instead of `define` would mean that Tempest falls back to the AST, and treats them as if they are slots of `<x-container>`.

```html x-header.view.php
<header>
    <x-slot name="top" />
    <x-container>
        <x-slot define="left"> <!-- define this slot as a slot of x-header, compiled and passed into x-container's default slot -->
            <x-header-left />
        </x-slot>
        <div>
            I am in the center
        </div> 
        <x-slot define="right">  <!-- define this slot as a slot of x-header, compiled and passed into x-container's default slot -->
            <x-header-right />
        </x-slot>
    </x-container>
    <x-slot name="bottom" />
</header>
```

At the callsite, you still use the `name` attribute to define which slot you're placing content into:

```html callsite.view.php
<x-header>
    <x-slot name="left">Some content I want to insert</x-slot>
</x-header>
```

This example would replace the default content `<x-header-left />` instead with the literal string `Some content I want to insert` - or whatever you provide.

#### Populating a child's name slot using `define`

You can also push content into a child's named slot, not just the default slot, by creating a `define`d slot as follows:

```html x-outer.view.php
<div class="outer">
    <x-inner>
        <x-slot name="left">
            <x-slot define="left">default-left-content</x-slot>
        </x-slot>
    </x-inner>
</div>
```

Again, the `define` keyword registers a `left` slot against the view component `<x-outer>` irrespective of it's position in the AST, and means that at the callsite:

```html outercallsite.view.php
<x-outer>
    <x-slot name="left">My override</x-slot>
</x-outer>
```

And so, this places the literal string `My override` into `<x-innner>`'s `left` slot.

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

will render

```html
<svg class="size-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
    <path
        fill="currentcolor"
        d="m3 15v9h3.5q.6 0 1.05.45t8 10.5v1q0 .6-.45 1.05t6.5 13h-2v2zm6.5 0v9h11v2h2v9h1.5v6h13v-2.5h-2v15zm7 0v9h20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05t20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"
    />
</svg>
```

This component includes some optional props you can use to control width and height. As a fallback, if you specify no class, no style, no width & height, the component will render a default width and height, but you can override this behaviour in any of the following ways.

```html
<x-icon name="material-symbols:php" />
```

will render

```html
<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
    <path
        fill="currentcolor"
        d="m3 15v9h3.5q.6 0 1.05.45t8 10.5v1q0 .6-.45 1.05t6.5 13h-2v2zm6.5 0v9h11v2h2v9h1.5v6h13v-2.5h-2v15zm7 0v9h20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05t20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"
    />
</svg>
```

Firstly, you can set width and height using Tailwind or custom classes. As long as you pass the `class` prop, the component will assume you are providing suitable dimensions, and will not check, or assert, any default dimensions.

```html
<x-icon name="material-symbols:php" class="w-[24px] h-[24px] text-indigo-400" />
```

will render

```html
<svg class="w-[24px] h-[24px] text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
    <path
        fill="currentcolor"
        d="m3 15v9h3.5q.6 0 1.05.45t8 10.5v1q0 .6-.45 1.05t6.5 13h-2v2zm6.5 0v9h11v2h2v9h1.5v6h13v-2.5h-2v15zm7 0v9h20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05t20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"
    />
</svg>
```

Secondly, if you aren't using Tailwind, or wish to set for a single icon without making a class, you can instead pass dimensions via the `style` prop. Again, as long as you pass `style`, the component will assume you are providing suitable dimensions, and will not check, or assert, any default dimensions.

```html
<x-icon name="material-symbols:php" style="width: 24px; height: 24px;" />
```

will render

```html
<svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
    <path
        fill="currentcolor"
        d="m3 15v9h3.5q.6 0 1.05.45t8 10.5v1q0 .6-.45 1.05t6.5 13h-2v2zm6.5 0v9h11v2h2v9h1.5v6h13v-2.5h-2v15zm7 0v9h20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05t20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"
    />
</svg>
```

Finally, you may provide the width and height properties directly with the props `width` and `height`. The component requires both to be set, or will render the fallback dimensions.

```html
<x-icon name="material-symbols:php" width="24px" height="24px" />
```

will render

```html
<svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24">
    <path
        fill="currentcolor"
        d="m3 15v9h3.5q.6 0 1.05.45t8 10.5v1q0 .6-.45 1.05t6.5 13h-2v2zm6.5 0v9h11v2h2v9h1.5v6h13v-2.5h-2v15zm7 0v9h20q.6 0 1.05.45t.45 1.05v1q0 .6-.45 1.05t20 13h-2v2zm-12-3.5h2v-1h-2zm13.5 0h2v-1h-2z"
    />
</svg>
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
