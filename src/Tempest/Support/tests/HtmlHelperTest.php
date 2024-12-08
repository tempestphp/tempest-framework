<?php

declare(strict_types=1);

namespace Tempest\Support\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Support\HtmlHelper;

/**
 * @internal
 */
final class HtmlHelperTest extends TestCase
{
    public function test_create_tag(): void
    {
        $this->assertSame(
            expected: '<div></div>',
            actual: HtmlHelper::createTag('div'),
        );

        $this->assertSame(
            expected: '<button type="submit">OK</button>',
            actual: HtmlHelper::createTag('button', ['type' => 'submit'], 'OK'),
        );

        $this->assertSame(
            expected: '<a href="https://example.com">Link</a>',
            actual: HtmlHelper::createTag('a', ['href' => 'https://example.com'], 'Link'),
        );

        $this->assertSame(
            expected: '<script src="https://example.com/script.js"></script>',
            actual: HtmlHelper::createTag('script', ['src' => 'https://example.com/script.js']),
        );

        $this->assertSame(
            expected: '<link href="https://example.com/style.css" rel="stylesheet" />',
            actual: HtmlHelper::createTag('link', ['href' => 'https://example.com/style.css', 'rel' => 'stylesheet']),
        );

        $this->assertSame(
            expected: '<img src="https://example.com/image.jpg" alt="An image" />',
            actual: HtmlHelper::createTag('img', ['src' => 'https://example.com/image.jpg', 'alt' => 'An image']),
        );

        $this->assertSame(
            expected: '<input type="checkbox" checked />',
            actual: HtmlHelper::createTag('input', ['type' => 'checkbox', 'checked' => true]),
        );

        $this->assertSame(
            expected: '<input type="checkbox" />',
            actual: HtmlHelper::createTag('input', ['type' => 'checkbox', 'checked' => false]),
        );
    }
}
