---
title: Markdown
description: "Fast and extensible Markdown in PHP"
---

`tempest/markdown` is a Markdown parser written in PHP. It's designed to be fast and extensible and has a bunch of additional features built-in like code highlighting, responsive images, tables, and frontmatter support.

## Quickstart

```sh
composer require tempest/markdown
```

Render Markdown like this:

```php
use Tempest\Markdown\Markdown;

$markdown = new Markdown();

$parsed = $markdown->parse(file_get_contents('README.md'));

echo $parsed->frontMatter['title'];
echo $parsed->html;
```

## Integrations

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

Language definitions work in both inline and pre code blocks:

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

### Responsive images

`tempest/markdown` has support for responsive images powered by [`tempest/responsive-image`](/docs/packages/responsive-image). You'll need to configure the responsive image factory before being able to use it.

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

## Performance

This package began as a challenge to make a more performant Markdown parser in pure PHP. The primary performance gain is from not relying on regex but instead using a simple lexer to tokenize Markdown files and convert them to HTML.

Benchmarks are included in this repo and can be run with `composer bench` after installing all dev dependencies. Here are the results on a local machine rendering the full Tempest docs:

| Package                | Memory   | Time to parse |
|------------------------|----------|---------------|
| tempest/markdown       | 5.944mb  | 6.281ms       |
| league/commonmark      | 21.114mb | 56.993ms      |
| michelf/php-markdown   | 7.343mb  | 23.215ms      |
| erusev/parsedown-extra | 8.485mb  | 15.163ms      |

