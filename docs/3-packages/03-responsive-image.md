---
title: Responsive images
description: "A standalone package to render responsive images"
---


## Quickstart

```console
composer require tempest/responsive-image
```

```php
use Tempest\ResponsiveImage\ResponsiveImageFactory;
use Tempest\ResponsiveImage\ResponsiveImageConfig;

$config = new ResponsiveImageConfig(
    srcPath: __DIR__ . '/path/to/image/sources',
    publicPath: __DIR__ . '/../public',
);

$imageFactory = new ResponsiveImageFactory($config);

$image = $imageFactory->create('/parrot.jpg');

echo $image->html;
```

```html
<img src="/parrot.jpg" srcset="/parrot-1920-1280.jpg 1920w, /parrot-1606-1070.jpg 1606w, /parrot-1214-809.jpg 1214w, /parrot-607-404.jpg 607w">
```

## In depth

The goal of this package is to render [responsive images for better web performance](https://developer.mozilla.org/en-US/docs/Web/HTML/Guides/Responsive_images). You provide it with a single image source, and this package will generate several responsive downscaled versions of that image. It will then move all images to the correct place and render the image HTML for you. Optionally, you can use [tempest/command-bus](/docs/features/command-bus) to generate responsive images in the background.

### Basic setup

Responsive images are generated with the `Tempest\ResponsiveImage\ResponsiveImageFactory` class. It takes one argument: a `Tempest\ResponsiveImage\ResponsiveImageConfig` object. This object looks like this:

```php
use Tempest\ResponsiveImage\ResponsiveImageConfig;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

$config = new ResponsiveImageConfig(
    srcPath: __DIR__ . '/../resources/src/',
    publicPath: __DIR__ . '/../public/',
    async: true,
    cache: true,
    imageManager: new ImageManager(new Driver()),
);
```
| Parameter                      | Description                                                                                                                                                                                                                |
|--------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `{:hl-property:srcPath:}`      | The path to the directory where all source images are stored.                                                                                                                                                              |
| `{:hl-property:publicPath:}`   | The path to the public directory where rendered images should be served from.                                                                                                                                              |
| `{:hl-property:async:}`        | Whether responsive images should be generated in the background. This paramater is only taken into account if Tempest's command bus is installed.                                                                          |
| `{:hl-property:cache:}`        | Whether generated responsive variants should be cached. If true, then responsive variants won't be generated as long as the main image file exists in the public path. Cache clearing should be done manually on your end. |
| `{:hl-property:imageManager:}` | The Intervention imagemanager. Refer to the [Intervention docs](https://image.intervention.io/v4) for all options.                                                                                                         |

### Image rendering

With a `ResponsiveImageFactory` in place, you can now render images. The only thing you need to do is pass it the image source (like it would be used in the HTML tag), and the factory will handle the rest. The final result will be an `Image` object that can be rendered to HTML.

```php
$imageFactory = new ResponsiveImageFactory($config);

$image = $imageFactory->create('/parrot.jpg'); 
// This image `/parrot.jpg` is assumed to be present in the defined `srcPath`.
// It will be copied, together with its responsive variants to `publicPath` 

echo $image->html;

// <img src="/parrot.jpg" srcset="/parrot-1920-1280.jpg 1920w, /parrot-1606-1070.jpg 1606w, /parrot-1214-809.jpg 1214w, /parrot-607-404.jpg 607w">
```

### Image rendering options

The factory will fill in and generate the image's `{:hl-property:srcset:}` for you based on image file sizes. However, you can also pass additional attributes to be added to the image's HTML:

```php
$image = $factory->create(
    src: '/parrot.jpg',
    alt: 'A parrot',
    sizes: [new Size(maxWidth: 1000, width: 300), new Size(maxWidth: 1500, width: 500)],
    lazy: true,
);
```

```html
<img
    src="/parrot.jpg"
    alt="A parrot"
    srcset="/parrot-1920-1280.jpg 1920w, /parrot-1606-1070.jpg 1606w, /parrot-1214-809.jpg 1214w, /parrot-607-404.jpg 607w"
    sizes="(max-width: 1000px) 300px, (max-width: 1500px) 500px"
    loading="lazy"
>
```

## Integrations

### Async image processing

You can combine this package with [tempest/command-bus](/docs/features/command-bus) to enable async image processing. This will make it so that the responsive variations of an image are rendered in the background. The main image will still be copied immediately, so you won't have to wait until processing is done.

Read about how to install Tempest's command bus as a standalone component [here](/docs/extra-topics/standalone-components#tempest-command-bus). 

### Markdown parsing

This package is used by [`tempest/markdown`](/docs/packages/markdown) to render responsive images from markdown files.