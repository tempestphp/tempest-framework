<?php

declare(strict_types=1);

namespace Tempest\Mcp\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Mcp\Content\Audio;
use Tempest\Mcp\Content\Blob;
use Tempest\Mcp\Content\Image;
use Tempest\Mcp\Content\ResourceLink;
use Tempest\Mcp\Content\Text;
use Tempest\Mcp\Exceptions\ContentWasNotSupported;

/**
 * @internal
 */
final class ContentTest extends TestCase
{
    #[Test]
    public function text_content(): void
    {
        $text = new Text('hello');

        $this->assertSame(['type' => 'text', 'text' => 'hello'], $text->toContent());
        $this->assertSame(
            ['uri' => 'demo://a', 'mimeType' => 'text/plain', 'text' => 'hello'],
            $text->toResourceContents('demo://a', null),
        );
        $this->assertSame(
            ['uri' => 'demo://a', 'mimeType' => 'text/html', 'text' => 'hello'],
            $text->toResourceContents('demo://a', 'text/html'),
        );
    }

    #[Test]
    public function image_content(): void
    {
        $image = new Image('raw-bytes', 'image/jpeg');

        $this->assertSame(
            ['type' => 'image', 'data' => base64_encode('raw-bytes'), 'mimeType' => 'image/jpeg'],
            $image->toContent(),
        );
        $this->assertSame(
            ['uri' => 'demo://a', 'mimeType' => 'image/jpeg', 'blob' => base64_encode('raw-bytes')],
            $image->toResourceContents('demo://a', 'application/octet-stream'),
        );
        $this->assertSame(
            ['uri' => 'demo://a', 'mimeType' => 'image/webp', 'blob' => base64_encode('raw-bytes')],
            new Image('raw-bytes')->toResourceContents('demo://a', 'image/webp'),
        );
        $this->assertSame(
            ['uri' => 'demo://a', 'mimeType' => 'image/png', 'blob' => base64_encode('raw-bytes')],
            new Image('raw-bytes')->toResourceContents('demo://a', null),
        );
    }

    #[Test]
    public function audio_content(): void
    {
        $audio = new Audio('raw-bytes');

        $this->assertSame(
            ['type' => 'audio', 'data' => base64_encode('raw-bytes'), 'mimeType' => 'audio/wav'],
            $audio->toContent(),
        );
        $this->assertSame(
            ['uri' => 'demo://a', 'mimeType' => 'audio/mpeg', 'blob' => base64_encode('raw-bytes')],
            $audio->toResourceContents('demo://a', 'audio/mpeg'),
        );
        $this->assertSame(
            ['uri' => 'demo://a', 'mimeType' => 'audio/flac', 'blob' => base64_encode('raw-bytes')],
            new Audio('raw-bytes', 'audio/flac')->toResourceContents('demo://a', 'audio/mpeg'),
        );
        $this->assertSame(
            ['uri' => 'demo://a', 'mimeType' => 'audio/wav', 'blob' => base64_encode('raw-bytes')],
            $audio->toResourceContents('demo://a', null),
        );
    }

    #[Test]
    public function blob_content(): void
    {
        $blob = new Blob('raw-bytes');

        $this->assertSame(
            ['uri' => 'demo://a', 'mimeType' => 'application/octet-stream', 'blob' => base64_encode('raw-bytes')],
            $blob->toResourceContents('demo://a', null),
        );

        $this->expectException(ContentWasNotSupported::class);

        $blob->toContent();
    }

    #[Test]
    public function resource_link_content(): void
    {
        $link = new ResourceLink(uri: 'demo://a', name: 'a', description: 'A resource', mimeType: 'text/plain');

        $this->assertSame(
            ['type' => 'resource_link', 'uri' => 'demo://a', 'name' => 'a', 'description' => 'A resource', 'mimeType' => 'text/plain'],
            $link->toContent(),
        );

        $this->assertSame(
            ['type' => 'resource_link', 'uri' => 'demo://b', 'name' => 'b'],
            new ResourceLink(uri: 'demo://b', name: 'b')->toContent(),
        );

        $this->expectException(ContentWasNotSupported::class);

        $link->toResourceContents('demo://a', null);
    }
}
