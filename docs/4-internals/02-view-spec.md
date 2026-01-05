---
title: View specifications
description: Read the technical specifications for Tempest View, our templating language.
---

Tempest View is a server-side templating engine powered by PHP. Most of its syntax is inspired by [Vue.js](https://vuejs.org/). Tempest View aims to stay as close as possible to HTML, using PHP where needed. All syntax builds on top of HTML and PHP so that developers don't need to learn any new syntax.

## Basic Syntax

### Expression attributes

Whenever an attribute starts with `:`, it's considered to be an expression attribute and its contents will be interpreted as PHP code. Common examples are control structures or data-passing.

```html
<div :if="$condition"></div>

<x-component :title="$content->title"></x-component>
```

### Escaped expression attributes

Some frontend frameworks also provide a `{html}:{:hl-property:attribute:}` syntax, these attributes can be escaped by using a double `::`:

```html
<div ::if="frontend-code"></div>
```

### Control structures

Control structures like conditionals and loops are modelled with expression attributes. These control structure attributes are available: `{html}:{:hl-property:if:}`, `{html}:{:hl-property:elseif:}`, `{html}:{:hl-property:else:}`, {:hl-property:isset:}`, `{html}:{:hl-property:foreach:}`, `{html}:{:hl-property:forelse:}`. Code within these control structures is compiled to valid PHP expressions.

The following conditional:

```html
<div :if="$condition">A</div>
<div :elseif="$otherCondition">B</div>
<div :else>C</div>
```

Will compile to:

```html
<?php if($condition) { ?>
    <div>A</div>
<?php } elseif ($otherCondition) { ?>
    <div>B</div>
<?php } else { ?>
    <div>C</div>
<?php } ?>
```

The following loop:

```html
<div :foreach="$items as $key => $item">
    A
</div>
<div :forelse>
    Nothing here
</div>
```

Will be compiled to:

```html
<?php if (iterator_count$items) { ?>
    <?php foreach ($items as $key => $item) { ?>
        <div>A</div>
    <?php } ?>
<?php } else { ?>
    Nothing here
<?php } ?>
```

### Combined control structures

Control structures can be combined and will be parsed in order:

```html
<div :foreach="$items as $key => $item" :if="$key !== 0">
    <!-- Never print the first item -->
</div>
```

### Echoing data

The `{{ $var }}` and `{!! $raw !!}` expressions can be used to write out escaped and raw data respectively. Anything within these expressions is interpreted as PHP:

```html
{{ strtoupper($var) }}
{!! $markdown->render($content) !!}
{{ uri([PostController::class, 'show'], post: $post->id) }}
```

### Comments

The `{html}{{-- --}}` expression is used to mark a block of code as comments. These comments will be stripped out server-side and not passed to the frontend. Normal HTML `{html}<!-- -->` comments can be used as client-side comments.

### Imports

Tempest will merge all imports at the top of the compiled view, meaning that each view can import any reference it needs:

```html
<?php
use App\PostController;
use function Tempest\Router\uri;
?>

{{ uri([PostController::class, 'show'], post: $post->id) }}
```

### View file resolution

Tempest views can be returned from a controller with data passed into them via named arguments:

```php
return view(__DIR__ . '/views/home.view.php', title: 'foo', description: 'bar');
return view('./views/home.view.php', title: 'foo', description: 'bar');
return view('views/home.view.php', title: 'foo', description: 'bar');
```

Tempest will search for view files according to the following rules:

- View files always end with `.view.php`
- First we check whether the view path as-is exists (absolute paths, eg. when using `__DIR__`)
- If not, we'll check whether the view file can be found relative to the controller's location
- If not, we'll search all discovery locations for the given path

### View objects

instead of using a `.view.php` file directly, developers can opt to create custom view objects. These objects implement the {b`\Tempest\View\View`} interface and expose their public properties and methods to their associated view:

```php
use Tempest\View\View;
use Tempest\View\IsView;

final class BookView implements View
{
    use IsView;

    public function __construct(
        public string $title,
        public Book $book,
    ) {
        $this->path = __DIR__  . '/books.view.php';
    }
    
    public function summarize(Book $book): string 
    {
        return // …
    }
}
```

```html
<h1>{{ $title }}</h1>

<div :foreach="$book->relatedBooks as $relatedBook">
    {{ $this->summarize($relatedBook) }}
</div>
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

## View components

Both template inclusion and inheritance with tempest/view is handled with html components. Any view file starting with `x-` will be considered to be a view component. View components are written as normal HTML elements, but can pass server-side variables between them in the form of normal and expression attributes.

### Registering view components

To create a view component, create a `.view.php` file that starts with `x-`. These files are referred to as anonymous view components and are automatically discovered by Tempest.

```html app/x-base.view.php
<html lang="en">
	<head>
		<title :if="$title">{{ $title }} — AirAcme</title>
		<title :else>AirAcme</title>
	</head>
	<body>
		<x-slot />
	</body>
</html>
```

### Using view components

All views may include a views components. In order to do so, you may simply use a component's name as a tag, including the `x-` prefix:

```html app/home.view.php
<x-base :title="$this->post->title">
	<article>
		{{ $this->post->body }}
	</article>
</x-base>
```

The example above demonstrates how to pass data to a component using an [expression attribute](#expression-attributes), as well as how pass elements as children if that component where the `<x-slot />` tag is used.

### Attributes in components

Attributes and [expression attributes](#expression-attributes) may be passed into view components. They work the same way as normal elements, and their values will be available in variables of the same name:

```html home.view.php
<x-base :title="$this->post->title">
	// ...
</x-base>
```

```html x-base.view.php
// ...
<title :if="$title">{{ $title }}</title>
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

Note that attributes names use `{txt}kebab-case`.

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

```html x-component.view.php
<div>
    <x-slot>Fallback value</x-slot>
    <x-slot name="a">Fallback value for named slot</x-slot>
</div>
```

```html
<x-component />

<!-- Will render "Fallback value" and "Fallback value for named slot" -->
```

### Named slots

When a single slot is not enough, names can be attached to them. When using a component with named slot, you may use the `<x-slot>` tag with a `name` attribute to render content in a named outlet:

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

The above example uses a slot named `styles` in its `<head>` element. The `<body>` element has a default, unnamed slot. A view component may use `<x-base>` and optionally refer to the `styles` slot using the syntax mentionned above, or simply provide content that will be injected in the default slot:

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

On some occasions, you might want to dynamically render view components, ie. render a view component whose name is determined at runtime. You can use the `{html}<x-component :is="">` element to do so:

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

Besides components that you may create yourself, Tempest provides a default set of useful built-in components to improve your developer experience.

All meta-data about discovered view components can be retrieved via the hidden `meta:view-component` command.

```console
./tempest meta:view-component [view-component]
```

```json
{
	"file": "/…/tempest-framework/packages/view/src/Components/x-markdown.view.php",
	"name": "x-markdown",
	"slots": [],
	"variables": [
		{
			"type": "string|null",
			"name": "$content",
			"attributeName": "content",
			"description": "The markdown content from a variable"
		}
	]
}
```

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

This component simply inject registered entrypoints where it is called.

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

### `x-template`

See [Templates](#templates).

### `x-slot`

See [Using slots](#using-slots).

### `x-markdown`

A component that will render markdown contents:

```html
<x-markdown># hi</x-markdown>
<x-markdown :content="$text" />
```

### `x-component`

A reserved component to render dynamic view components:

```html
<x-component is="x-post" :title="$title">
    Content
</x-component>
```

The attributes and content of dynamic components are passed to the underlying component.

## Possible IDE integrations

This section lists a bunch of ideas for IDE features that would be useful for IDE integrations.

### Click-through view files

Clicking a view file path leads to the view:

```php
return view(__DIR__ . '/views/home.view.php');
return view('views/home.view.php');
```

### View data autocompletion:

```php
return view(__DIR__ . '/views/home.view.php', foo: 'Foo', bar: 'Bar');
```

`$foo` and `$bar` are available as variables within `__DIR__ . '/views/home.view.php'`.

```php
return view(__DIR__ . '/views/home.view.php', book: new Book(/* … */));
```

`$book` is available in the view, and its type known for autocompletion.

### Auto-import symbols

Referencing a symbol within a view will automatically import it at the top of the file.

```html
<?php
use App\PostController;
use function Tempest\Router\uri;
?>

{{ uri([PostController::class, 'show'], post: $post->id) }}
```

### Loop variable autocompletion

```html
<div :foreach="$items as $key => $item">
    {{ $item }} {{-- Autocomplete here --}}
</div>
```

### View component autocompletion

```html
<x-book :title="$book->title"></x-book>

{{-- `$title` is available in the `x-book` component  --}}
```

### Click-through view components

cmd/ctrl+click on a view component's tag will open the associated view component file.

### Auto-comment selected text

```html
{{-- this text was selected then commented out via a keyboard shortcut --}}
```

### Cycle between comment types

Pressing the same keyboard short twice will toggle between server-side and client-side comments

```html
{{-- this text was selected then commented out via a keyboard shortcut --}} — First press
<!-- this text was selected then commented out via a keyboard shortcut --> — Second press
this text was selected then commented out via a keyboard shortcut — Third press, reverts back to normal
```
