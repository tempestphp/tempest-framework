<?php

namespace Tests\Tempest\Integration\Mailer;

use Tempest\Mail\Attachment;
use Tempest\Mail\Attachments\StorageAttachment;
use Tempest\Mail\Exceptions\FileAttachmentWasNotFound;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class AttachmentTest extends FrameworkIntegrationTestCase
{
    public function test_from_storage(): void
    {
        $storage = $this->storage->fake();
        $storage->write('file.txt', 'hello');

        $attachment = Attachment::fromStorage($storage, 'file.txt');

        $this->assertSame('file.txt', $attachment->name);
        $this->assertSame('text/plain', $attachment->contentType);
        $this->assertSame('hello', stream_get_contents(($attachment->resolve)()));
    }

    public function test_from_storage_fails_if_file_does_not_exist(): void
    {
        $this->expectException(FileAttachmentWasNotFound::class);

        $storage = $this->storage->fake();

        Attachment::fromStorage($storage, 'file.txt');
    }

    public function test_from_filesystem(): void
    {
        $attachment = Attachment::fromFilesystem(__DIR__ . '/Fixtures/attachment.txt');

        $this->assertSame('attachment.txt', $attachment->name);
        $this->assertSame('text/plain', $attachment->contentType);
        $this->assertStringContainsStringIgnoringLineEndings("hello\n", ($attachment->resolve)());
    }

    public function test_from_path_throws_when_file_does_not_exist(): void
    {
        $this->expectException(FileAttachmentWasNotFound::class);

        Attachment::fromFilesystem('./file.txt');
    }

    public function test_from_closure(): void
    {
        $attachment = Attachment::fromClosure(
            fn () => 'Hello, world!',
            'greeting.txt',
            'text/plain',
        );

        $this->assertSame('greeting.txt', $attachment->name);
        $this->assertSame('text/plain', $attachment->contentType);
        $this->assertSame('Hello, world!', ($attachment->resolve)());
    }
}
