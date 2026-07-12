---
title: Markdown
description: "Fast and extensible Markdown in PHP"
---

`tempest/markdown` is a Markdown parser for server-side Markdown parsing with PHP. It's designed to be fast and extensible, and has a bunch of extended Markdown features built-in like code highlighting, table and div support, responsive images, and frontmatter support.

## Quickstart

```console
composer require tempest/markdown
```

Render Markdown like this:

```php
use Tempest\Markdown\Markdown;

$markdown = new Markdown();

$parsed = $markdown->parse(file_get_contents('README.md'));

echo $parsed->frontmatter['title'];
echo $parsed->html;
```

## Extended features

One thing that sets `tempest/markdown` apart from other Markdown implementations is that it comes with a bunch of extended features built-in.

### Code highlighting

`tempest/markdown` comes with code highlighting out of the box powered by [`tempest/highlight`](/docs/packages/highlight). You can configure the highlighter by passing a new instance into the markdown parser:

```php
use Tempest\Markdown\Markdown;
use Tempest\Highlight\Highlighter;

$markdown = new Markdown(
    highlighter: new Highlighter(
        // Configure theme, etc
    ),
);
```

Language definitions work for both inline and multiline code blocks:

<pre>
This is an inline PHP codeblock: `{php}echo "Hello";`
</pre>

<pre>
This is a pre PHP codeblock:

&#96;&#96;&#96;php
echo "world";
&#96;&#96;&#96;
</pre>

You can disable all code highlighting by passing in `{php}null`:

```php
$markdown = new Markdown(
    highlighter: null,
);
```

### Frontmatter

Add frontmatter like you're used to with other Markdown parsers

```md
---
title: Markdown
description: "Fast and extensible Markdown in PHP"
---

`tempest/markdown` is a Markdown parser for server-side Markdown parsing with PHP. It's designed to be fast and extensible, and has a bunch of extended Markdown features built-in like code highlighting, table and div support, responsive images, and frontmatter support.
```

```php
$parsed = $markdown->parse($contents);

echo $parsed->frontmatter['title'];
```

### Responsive images

`tempest/markdown` has support for responsive images powered by [`tempest/responsive-image`](/docs/packages/responsive-image). You'll need to configure the responsive image factory to enable this feature.

```php
use Tempest\Markdown\Markdown;
use Tempest\ResponsiveImage\ResponsiveImageConfig;
use Tempest\ResponsiveImage\ResponsiveImageFactory;

$imageConfig = new ResponsiveImageConfig(
    srcPath: __DIR__ . '/../resources/images',
    publicPath: __DIR__ . '/../public',
);

$markdown = new Markdown(
    imageFactory: new ResponsiveImageFactory($imageConfig),
);
```

When enabled, `tempest/markdown` will generate different versions of the same image, and add them as `srcset` entries in the generate HTML.

```md
![A parrot](/parrot.jpg)
```

```html
<img 
    src="/parrot.jpg"
    alt="A parrot"
    srcset="/parrot-1920-1280.jpg 1920w, /parrot-1606-1070.jpg 1606w, /parrot-1214-809.jpg 1214w, /parrot-607-404.jpg 607w"
>
```

Read more about responsive images [here](/docs/packages/responsive-image).

### Tables

Add tables like you're used to with other Markdown parsers.

```
| Package                | Memory   | Time to parse |
|------------------------|----------|---------------|
| tempest/markdown       | 6.664mb  | 10.906ms      |
| league/commonmark      | 21.114mb | 56.993ms      |
| michelf/php-markdown   | 7.343mb  | 23.215ms      |
| erusev/parsedown-extra | 8.485mb  | 15.163ms      |
```

```html

<table>
    <thead>
    <tr>
        <th>Package</th>
        <th>Memory</th>
        <th>Time to parse</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>tempest/markdown</td>
        <td>6.664mb</td>
        <td>10.906ms</td>
    </tr>

    <!-- … -->

    </tbody>
</table>
```

### Divs

Use `:::` to create divs with optional classes:

```
:::alert
This is an important message!
:::
```

```html
<div class="alert">
    This is an important message!
</div>
```

### Strikethrough

Use `~~` to strikethrough text:

```
~~This was wrong~~
```

```html
<s>This was wrong</s>
```

### Target blank links

Prepend `*` to a link's URI to open the link in a new tab:

```
[Click me](*https://stitcher.io)
```

```html
<a href="https://stitcher.io" target="_blank" rel="noopener noreferrer">Click me</a>
```

### Raw snippets

Wrap anything in `@@` to prevent it from being rendered at all:

```md
@@
## Hello
@@
```

```html
## Hello
```

## Adding custom features

`tempest/markdown` is meant to be extended. Adding custom parser rules is done in two steps: first you provide a `Rule`, this is a class that determines when your custom parsing logic should be triggered. Next you'll use a `Token` to render your selected Markdown code in any way you'd like.

Let's work with an example. Say you want to add support for including custom HTML snippets. It could look something like this: 

```
Hello world

{{ snippets/call-to-action.html }}
```

The first step to adding this new feature is detecting when we run into our custom `{{ }}` syntax. This is done with a `Rule`. Let's call our implementation `SnippetRule`:

```php
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;

final readonly class SnippetRule implements Rule
{
    public function shouldLex(Parser $parser): bool
    {
        // Our rule takes effect as soon as we run into `{{`
        
        return $parser->comesNext('{{', length: 2);
    }

    public function lex(Parser $parser): ?Token
    {
        // We'll consume all { characters
        $parser->consumeWhile('{');

        // Then we'll consume and store the snippet path itself until we encounter the closing } characters
        $snippet = $parser->consumeUntil('}');

        // Then we'll consume the closing } characters
        $parser->consumeWhile('}');
        
        // Finally, we return a token with the snippet
        return new SnippetToken(trim($snippet));
    }
}
```

:::info
For performance reasons, it's best to explcitly add the `{:hl-property:length:}` parameter to the `{:hl-property:comesNext:}` call. It's not required, but it will make parsing faster. If possible, also try not to rely on regex within your parser rules, as it may become a performance bottleneck.
:::

So that's our rule implementation: we consumed our custom `{{ path }}` syntax, and created a token with that path. Let's take a look at the token implementation next.

The token's responsibility is to parse the content into HTML. 

```php
use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;
use Tempest\View\Exceptions\ViewNotFound;
use Tempest\View\ViewRenderer;
use function Tempest\Container\get;

final readonly class SnippetToken implements Token
{
    public function __construct(
        public string $path,
    ) {}

    public function parse(Parser $parser): string
    {
        // Of course, you should add validation here depending on your use case
        
        return file_get_contents($this->path);
    }
}
```

Finally, you should add your custom rule to the Markdown parser:

```php
use Tempest\Markdown\Markdown;

$markdown = new Markdown();

$markdown->prependRules(
    new SnippetRule(),
);
```

If needed, you can fully customize the Markdown parser by overwriting all rules:

```php
$markdown = new Markdown()->withRules(
    new NewLineRule(),
    new FrontMatterRule(),
    new HeadingRule(),
    new QuoteRule(),
    new PreRule(),
    new DivRule(),
    new ThinRulerRule(),
    new ThickRulerRule(),
    new ListRule(),
    new OrderedListRule(),
    new TableRule(),
    new HtmlRule(),
    new ParagraphRule(),
);
```

This way you have full control over how the parser works. For more inspiration, you can look at the [rules that come built-in with the package](https://github.com/tempestphp/markdown/tree/main/src/Rules).

## Performance

This package began as a challenge to make a more performant Markdown parser in pure PHP. The primary performance gain is from not relying on regex but instead using a simple lexer to tokenize Markdown files and convert them to HTML.

Benchmarks are included in this repo and can be run with `composer bench` after installing all dev dependencies. Here are the results on a local machine rendering the full Tempest docs:

| Package                | Memory   | Time to parse |
|------------------------|----------|---------------|
| tempest/markdown       | 6.664mb  | 10.906ms      |
| league/commonmark      | 21.114mb | 56.993ms      |
| michelf/php-markdown   | 7.343mb  | 23.215ms      |
| erusev/parsedown-extra | 8.485mb  | 15.163ms      |

