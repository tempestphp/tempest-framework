<?php

namespace Tests\Tempest\Integration\Mailer;

use Tempest\Mail\Attachments\StorageAttachment;
use Tempest\Mail\Exceptions\FileAttachmentWasNotFound;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class StorageAttachmentTest extends FrameworkIntegrationTestCase
{
    public function test_from_path(): void
    {
        $storage = $this->storage->fake();
        $storage->write('file.txt', 'hello');

        $attachment = StorageAttachment::fromPath('file.txt');

        $this->assertSame('file.txt', $attachment->name);
        $this->assertSame('text/plain', $attachment->contentType);
    }

    public function test_from_path_fails_if_file_does_not_exist(): void
    {
        $this->expectException(FileAttachmentWasNotFound::class);

        $this->storage->fake();

        StorageAttachment::fromPath('file.txt');
    }
}
