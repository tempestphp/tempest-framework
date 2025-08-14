---
title: Highlight
description: "Tempest's highlighter is a package for server-side, high-performance, and flexible code highlighting."
---

## Quickstart

Require `tempest/highlight` with composer:

```
composer require tempest/highlight
```

And highlight code like this:

```php
$highlighter = new \Tempest\Highlight\Highlighter();

$code = $highlighter->parse($code, 'php');
```

## Supported languages

All supported languages can be found in the [GitHub repository](https://github.com/tempestphp/highlight/tree/main/src/Languages).

## Themes

There are a [bunch of themes](https://github.com/tempestphp/highlight/tree/main/src/Themes/Css) included in this package. You can load them either by importing the correct CSS file into your project's CSS file, or you can manually copy a stylesheet.

```css
@import "../../../../../vendor/tempest/highlight/src/Themes/Css/highlight-light-lite.css";
```

You can build your own CSS theme with just a couple of classes, copy over [the base stylesheet](https://github.com/tempestphp/highlight/tree/main/src/Themes/Css/highlight-light-lite.css), and make adjustments however you like. Note that `pre` tag styling isn't included in this package.

### Inline themes

If you don't want to or can't load a CSS file, you can opt to use the `InlineTheme` class. This theme takes the path to a CSS file, and will parse it into inline styles:

```php
$highlighter = new Highlighter(new InlineTheme(__DIR__ . '/../src/Themes/Css/solarized-dark.css'));
```

### Terminal themes

Terminal themes are simpler because of their limited styling options. Right now there's one terminal theme provided: `LightTerminalTheme`. More terminal themes are planned to be added in the future.

```php
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Themes\LightTerminalTheme;

$highlighter = new Highlighter(new LightTerminalTheme());

echo $highlighter->parse($code, 'php');
```

![](/img/terminal.png)

## Gutter

This package can render an optional gutter if needed.

```php
$highlighter = new Highlighter()->withGutter(startAt: 10);
```

The gutter will show additions and deletions, and can start at any given line number:

```php{10}
  public function before(TokenType $tokenType): string
  {
      $style = match ($tokenType) {
{-          TokenType::KEYWORD => TerminalStyle::FG_DARK_BLUE,
          TokenType::PROPERTY => TerminalStyle::FG_DARK_GREEN,
          TokenType::TYPE => TerminalStyle::FG_DARK_RED,-}
          TokenType::GENERIC => {+TerminalStyle::FG_DARK_CYAN+},
          TokenType::VALUE => TerminalStyle::FG_BLACK,
          TokenType::COMMENT => TerminalStyle::FG_GRAY,
          TokenType::ATTRIBUTE => TerminalStyle::RESET,
      };

      return TerminalStyle::ESC->value . $style->value;
  }
```

Finally, you can enable gutter rendering on the fly if you're using [commonmark code blocks](#common-mark-integration) by appending <code>{startAt}</code> to the language definition:

<pre>
&#96;&#96;&#96;php{10}
echo 'hi'!
&#96;&#96;&#96;
</pre>

```php{10}
echo 'hi'!
```

## Special highlighting tags

This package offers a collection of special tags that you can use within your code snippets. These tags won't be shown in the final output, but rather adjust the highlighter's default styling. All these tags work multi-line, and will still properly render its wrapped content.

Note that highlight tags are not supported in terminal themes.

### Emphasize, strong, and blur

You can add these tags within your code to emphasize or blur parts:

- <code>{_ content _}</code> adds the <code>.hl-em</code> class
- <code>{* content *}</code> adds the <code>.hl-strong</code> class
- <code>{~ content ~}</code> adds the <code>.hl-blur</code> class

<pre>
{_Emphasized text_}
{*Strong text*}
{~Blurred text~}
</pre>

This is the end result:

```txt
{_Emphasized text_}
{*Strong text*}
{~Blurred text~}
```

### Additions and deletions

You can use these two tags to mark lines as additions and deletions:

- <code>{+ content +}</code> adds the `.hl-addition` class
- <code>{- content -}</code> adds the `.hl-deletion` class

<pre>
{-public class Foo {}-}
{+public class Bar {}+}
</pre>

```php
{-public class Foo {}-}
{+public class Bar {}+}
```

As a reminder: all these tags work multi-line as well:

```php{1}
  public function before(TokenType $tokenType): string
  {
      $style = match ($tokenType) {
{-          TokenType::KEYWORD => TerminalStyle::FG_DARK_BLUE,
          TokenType::PROPERTY => TerminalStyle::FG_DARK_GREEN,
          TokenType::TYPE => TerminalStyle::FG_DARK_RED,
          TokenType::GENERIC => TerminalStyle::FG_DARK_CYAN,
          TokenType::VALUE => TerminalStyle::FG_BLACK,
          TokenType::COMMENT => TerminalStyle::FG_GRAY,
          TokenType::ATTRIBUTE => TerminalStyle::RESET,-}
      };

      return TerminalStyle::ESC->value . $style->value;
  }
```

### Custom classes

You can add any class you'd like by using the <code>{:classname: content :}</code> tag:

<pre>
&lt;style&gt;
.hl-a {
    background-color: #FFFF0077;
}

.hl-b {
    background-color: #FF00FF33;
}
&lt;/style&gt;

&#96;&#96;&#96;php
{:hl-a:public class Foo {}:}
{:hl-b:public class Bar {}:}
&#96;&#96;&#96;
</pre>

```php
{:hl-a:public class Foo {}:}
{:hl-b:public class Bar {}:}
```

### Inline languages

Within inline Markdown code tags, you can specify the language by prepending it between curly brackets:

<pre>
&#96;{php}public function before(TokenType $tokenType): string&#96;
</pre>

You'll need to set up [commonmark](#common-mark-integration) properly to get this to work.

## CommonMark integration

If you're using `league/commonmark`, you can highlight codeblocks and inline code like so:

```php
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Tempest\Highlight\CommonMark\HighlightExtension;

$environment = new Environment();

$environment
    ->addExtension(new CommonMarkCoreExtension())
    ->addExtension(new HighlightExtension());

$markdown = new MarkdownConverter($environment);
```

Keep in mind that you need to manually install `league/commonmark`:

```
composer require league/commonmark;
```

## Implementing a custom language

Let's explain how `tempest/highlight` works by implementing a new language — [Blade](https://laravel.com/docs/11.x/blade) is a good candidate. It looks something like this:

```blade
@if(! empty($items))
    <div class="container">
        Items: {{ count($items) }}.
    </div>
@endslot
```

In order to build such a new language, you need to understand _three_ concepts of how code is highlighted: _patterns_, _injections_, and _languages_.

### Patterns

A _pattern_ represents part of code that should be highlighted. A _pattern_ can target a single keyword like `return` or `class`, or it could be any part of code, like for example a comment: `/* this is a comment */` or an attribute: `#[Get(uri: '/')]`.

Each _pattern_ is represented by a simple class that provides a regex pattern, and a `TokenType`. The regex pattern is used to match relevant content to this specific _pattern_, while the `TokenType` is an enum value that will determine how that specific _pattern_ is colored.

Here's an example of a simple _pattern_ to match the namespace of a PHP file:

```php
use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\Tokens\TokenType;

final readonly class NamespacePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return 'namespace (?<match>[\w\\\\]+)';
    }

    public function getTokenType(): TokenType
    {
        return TokenType::TYPE;
    }
}
```

Note that each pattern must include a regex capture group that's named `match`. The content that matched within this group will be highlighted.

For example, this regex `namespace (?<match>[\w\\\\]+)` says that every line starting with `namespace` should be taken into account, but only the part within the named group `(?<match>…)` will actually be colored. In practice that means that the namespace name matching `[\w\\\\]+`, will be colored.

Yes, you'll need some basic knowledge of regex. Head over to [https://regexr.com/](https://regexr.com/) if you need help, or take a look at the existing patterns in this repository.

**In summary:**

- Pattern classes provide a regex pattern that matches parts of code.
- Those regexes should contain a group named `match`, which is written like so `(?<match>…)`, this group represents the code that will actually be highlighted.
- Finally, a pattern provides a `{php}TokenType`, which is used to determine the highlight style for the specific match.

### Injections

Once you've understood patterns, the next step is to understand _injections_. _Injections_ are used to highlight different languages within one code block. For example: HTML could contain CSS, which should be styled properly as well.

An _injection_ will tell the highlighter that it should treat a block of code as a different language. For example:

```html
<div>
    <x-slot name="styles">
        <style>
            body {
                background-color: red;
            }
        </style>
    </x-slot>
</div>
```

Everything within `{html}<style></style>` tags should be treated as CSS. That's done by injection classes:

```php
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Injection;
use Tempest\Highlight\IsInjection;
use Tempest\Highlight\ParsedInjection;

final readonly class CssInjection implements Injection
{
    use IsInjection;

    public function getPattern(): string
    {
        return '<style>(?<match>(.|\n)*)<\/style>';
    }

    public function parseContent(string $content, Highlighter $highlighter): ParsedInjection
    {
        return new ParsedInjection(
            content: $highlighter->parse($content, 'css')
        );
    }
}
```

Just like patterns, an _injection_ must provide a pattern. This pattern, for example, will match anything between style tags: `{html}<style>(?<match>(.|\n)*)<\/style>`.

The second step in providing an _injection_ is to parse the matched content into another language. That's what the `{php}parseContent()` method is for. In this case, we'll get all code between the style tags that was matched with the named `(?<match>…)` group, and parse that content as CSS instead of whatever language we're currently dealing with.

**In summary:**

- Injections provide a regex that matches a blob of code of language A, while in language B.
- Just like patterns, injection regexes should contain a group named `match`, which is written like so: `(?<match>…)`.
- Finally, an injection will use the highlighter to parse its matched content into another language.

### Languages

The last concept to understand: _languages_ are classes that bring _patterns_ and _injections_ together. Take a look at the `{php}HtmlLanguage`, for example:

```php
class HtmlLanguage extends BaseLanguage
{
    public function getName(): string
    {
        return 'html';
    }

    public function getAliases(): array
    {
        return ['htm', 'xhtml'];
    }

    public function getInjections(): array
    {
        return [
            ...parent::getInjections(),
            new PhpInjection(),
            new PhpShortEchoInjection(),
            new CssInjection(),
            new CssAttributeInjection(),
        ];
    }

    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
            new OpenTagPattern(),
            new CloseTagPattern(),
            new TagAttributePattern(),
            new HtmlCommentPattern(),
        ];
    }
}
```

This `{php}HtmlLanguage` class specifies the following things:

- PHP can be injected within HTML, both with the short echo tag `<?=` and longer `<?php` tags
- CSS can be injected as well, JavaScript support is still work in progress
- There are a bunch of patterns to highlight HTML tags properly

On top of that, it extends from `{php}BaseLanguage`. This is a language class that adds a bunch of cross-language injections, such as blurs and highlights. Your language doesn't _need_ to extend from `{php}BaseLanguage` and could implement `{php}Language` directly if you want to.

With these three concepts in place, let's bring everything together to explain how you can add your own languages.

### Adding custom languages

So we're adding [Blade](https://laravel.com/docs/11.x/blade) support. We could create a new language class and start from scratch, but it'd probably be easier to extend an existing language, `{php}HtmlLanguage` is probably the best. Let create a new `{php}BladeLanguage` class that extends from `{php}HtmlLanguage`:

```php
class BladeLanguage extends HtmlLanguage
{
    public function getName(): string
    {
        return 'blade';
    }

    public function getAliases(): array
    {
        return [];
    }

    public function getInjections(): array
    {
        return [
            ...parent::getInjections(),
        ];
    }

    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
        ];
    }
}
```

With this class in place, we can start adding our own patterns and injections. Let's start with adding a pattern that matches all Blade keywords, which are always prepended with the `@` sign. Let's add it:

```php
final readonly class BladeKeywordPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\@[\w]+)\b';
    }

    public function getTokenType(): TokenType
    {
        return TokenType::KEYWORD;
    }
}
```

And register it in our `{php}BladeLanguage` class:

```php
public function getPatterns(): array
{
    return [
        ...parent::getPatterns(),
        new BladeKeywordPattern(),
    ];
}
```

Next, there are a couple of places within Blade where you can write PHP code: within the `{blade}@php` keyword, as well as within keyword brackets: `{blade}@if (count(…))`. Let's write two injections for that:

```php
final readonly class BladePhpInjection implements Injection
{
    use IsInjection;

    public function getPattern(): string
    {
        return '\@php(?<match>(.|\n)*?)\@endphp';
    }

    public function parseContent(string $content, Highlighter $highlighter): ParsedInjection
    {
        return new ParsedInjection(
            content: $highlighter->parse($content, 'php')
        );
    }
}
```

```php
final readonly class BladeKeywordInjection implements Injection
{
    use IsInjection;

    public function getPattern(): string
    {
        return '(\@[\w]+)\s?\((?<match>.*)\)';
    }

    public function parseContent(string $content, Highlighter $highlighter): ParsedInjection
    {
        return new ParsedInjection(
            content: $highlighter->parse($content, 'php')
        );
    }
}
```

Let's add these to our `{php}BladeLanguage` class as well:

```php
public function getInjections(): array
{
    return [
        ...parent::getInjections(),
        new BladePhpInjection(),
        new BladeKeywordInjection(),
    ];
}
```

Next, you can write `{{ … }}` and `{!! … !!}` to echo output. Whatever is between these brackets is also considered PHP, so, one more injection:

```php
final readonly class BladeEchoInjection implements Injection
{
    use IsInjection;

    public function getPattern(): string
    {
        return '({{|{!!)(?<match>.*)(}}|!!})';
    }

    public function parseContent(string $content, Highlighter $highlighter): ParsedInjection
    {
        return new ParsedInjection(
            content: $highlighter->parse($content, 'php')
        );
    }
}
```

And, finally, you can write Blade comments like so: `{{-- --}}`, this can be a simple pattern:

```php
final readonly class BladeCommentPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\{\{\-\-(.|\n)*?\-\-\}\})';
    }

    public function getTokenType(): TokenType
    {
        return TokenType::COMMENT;
    }
}
```

With all of that in place, the only thing left to do is to add our language to the highlighter:

```php
$highlighter->addLanguage(new BladeLanguage());
```

And we're done! Blade support with just a handful of patterns and injections!

## Adding tokens

<style>
.hl-null {
    color: red;
}
</style>

Some people or projects might want more fine-grained control over how specific words are coloured. A common example are `null`, `true`, and `false` in json files. By default, `tempest/highlight` will treat those value as normal text, and won't apply any special highlighting to them:

```json
{
	"null-property": null,
	"value-property": "value"
}
```

However, it's super trivial to add your own, extended styling on these kinds of tokens. Start by adding a custom language, let's call it `ExtendedJsonLanguage`:

```php
use Tempest\Highlight\Languages\Json\JsonLanguage;

class ExtendedJsonLanguage extends JsonLanguage
{
    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
        ];
    }
}
```

Next, let's add a pattern that matches `null`:

```php
use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\Tokens\DynamicTokenType;
use Tempest\Highlight\Tokens\TokenType;

final readonly class JsonNullPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '\: (?<match>null)';
    }

    public function getTokenType(): TokenType
    {
        return new DynamicTokenType('hl-null');
    }
}
```

Note how we return a `{php}DynamicTokenType` from the `{php}getTokenType()` method. The value passed into this object will be used as the classname for this token.

Next, let's add this pattern in our newly created `{php}ExtendedJsonLanguage`:

```php
class ExtendedJsonLanguage extends JsonLanguage
{
    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
            {*new JsonNullPattern(),*}
        ];
    }
}
```

Finally, register `{php}ExtendedJsonLanguage` into the highlighter:

```php
$highlighter->addLanguage(new ExtendedJsonLanguage());
```

Note that, because we extended `{php}JsonLanguage`, this language will target all code blocks tagged as `json`. You could provide a different name, if you want to make a distinction between the default implementation and yours (this is what's happening on this page):

```php
class ExtendedJsonLanguage extends JsonLanguage
{
    public function getName(): string
    {
        return 'json_extended';
    }

    // …
}
```

There we have it!

```json_extended
{
    "null-property": null,
    "value-property": "value"
}
```

You can add as many patterns as you like, you can even make your own `{php}TokenType` implementation if you don't want to rely on `{php}DynamicTokenType`:

```php
enum ExtendedTokenType: string implements TokenType
{
    case VALUE_NULL = 'null';
    case VALUE_TRUE = 'true';
    case VALUE_FALSE = 'false';

    public function getValue(): string
    {
        return $this->value;
    }

    public function canContain(TokenType $other): bool
    {
        return false;
    }
}
```

## Opt-in features

`tempest/highlight` has a couple of opt-in features, if you need them.

### Markdown support

```
composer require league/commonmark;
```

```php
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Tempest\Highlight\CommonMark\HighlightExtension;

$environment = new Environment();

$environment
    ->addExtension(new CommonMarkCoreExtension())
    ->addExtension(new HighlightExtension(/* You can manually pass in configured highlighter as well */));

$markdown = new MarkdownConverter($environment);
```

### Word complexity

Ellison is a simple library that helps identify complex sentences and poor word choices. It uses similar heuristics to Hemingway, but it doesn't include any calls to third-party APIs or LLMs. Just a bit of PHP:

```ellison
The app highlights lengthy, complex sentences and common errors; if you see a yellow sentence, shorten or split it. If you see a red highlight, your sentence is so dense and complicated that your readers will get lost trying to follow its meandering, splitting logic — try editing this sentence to remove the red.

You can utilize a shorter word in place of a purple one. Click on highlights to fix them.

Adverbs and weakening phrases are helpfully shown in blue. Get rid of them and pick words with force, perhaps.

Phrases in green have been marked to show passive voice.
```

You can enable Ellison support by installing [`assertchris/ellison`](https://github.com/assertchris/ellison-php):

```
composer require assertchris/ellison
```

You'll have to add some additional CSS classes to your stylesheet as well:

```css
.hl-moderate-sentence {
	background-color: #fef9c3;
}

.hl-complex-sentence {
	background-color: #fee2e2;
}

.hl-adverb-phrase {
	background-color: #e0f2fe;
}

.hl-passive-phrase {
	background-color: #dcfce7;
}

.hl-complex-phrase {
	background-color: #f3e8ff;
}

.hl-qualified-phrase {
	background-color: #f1f5f9;
}

pre[data-lang="ellison"] {
	text-wrap: wrap;
}
```

The `ellison` language is now available:

<pre>
```ellison
Hello world!
```
</pre>

You can play around with it [here](/ellison).
