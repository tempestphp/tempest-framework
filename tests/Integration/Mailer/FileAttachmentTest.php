<?php

namespace Tests\Tempest\Integration\Mailer;

use Tempest\Mail\Attachments\FileAttachment;
use Tempest\Mail\Exceptions\FileAttachmentWasNotFound;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class FileAttachmentTest extends FrameworkIntegrationTestCase
{
    public function test_from_path(): void
    {
        $attachment = FileAttachment::fromPath(__DIR__ . '/Fixtures/attachment.txt');

        $this->assertSame('attachment.txt', $attachment->name);
        $this->assertSame('text/plain', $attachment->contentType);
    }

    public function test_from_path_throws_when_file_does_not_exist(): void
    {
        $this->expectException(FileAttachmentWasNotFound::class);

        FileAttachment::fromPath('./file.txt');
    }
}
