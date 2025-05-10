<?php

namespace Tempest\Mail\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Mail\Attachments\FileAttachment;
use Tempest\Mail\Builder\EmailBuilder;
use Tempest\Mail\Email;

final class EmailBuilderTest extends TestCase
{
    public function test_builder(): void
    {
        $email = new EmailBuilder()
            ->to('michael.scott@dundermifflin.com')
            ->cc(['dwight.schrute@dundermifflin.com', 'jim.halpert@dundermifflin.com'])
            ->bcc('pam.beesly@dundermifflin.com')
            ->withSubject('Important: Please come to my office right away')
            ->withFileAttachment(__DIR__ . '/Fixtures/attachment.txt')
            ->withText('Gotcha!')
            ->make();

        $this->assertInstanceOf(Email::class, $email);
        $this->assertContains('michael.scott@dundermifflin.com', $email->envelope->to);
        $this->assertContains('dwight.schrute@dundermifflin.com', $email->envelope->cc);
        $this->assertContains('jim.halpert@dundermifflin.com', $email->envelope->cc);
        $this->assertContains('pam.beesly@dundermifflin.com', $email->envelope->bcc);
        $this->assertSame('Important: Please come to my office right away', $email->envelope->subject);
        $this->assertSame('Gotcha!', $email->content->text);
        $this->assertCount(1, $email->content->attachments);

        $attachment = $email->content->attachments[0];

        $this->assertInstanceOf(FileAttachment::class, $attachment);
        $this->assertSame('attachment.txt', $attachment->name);
        $this->assertSame('text/plain', $attachment->contentType);
    }
}
