<?php

namespace Tempest\Mail\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Mail\Attachment;
use Tempest\Mail\Email;
use Tempest\Mail\EmailBuilder;
use Tempest\Mail\GenericEmail;

final class EmailBuilderTest extends TestCase
{
    public function test_generic_email_builds_emails(): void
    {
        $this->assertInstanceOf(EmailBuilder::class, GenericEmail::build());
    }

    public function test_builder(): void
    {
        $email = new EmailBuilder()
            ->to('michael.scott@dundermifflin.com')
            ->cc(['dwight.schrute@dundermifflin.com', 'jim.halpert@dundermifflin.com'])
            ->bcc('pam.beesly@dundermifflin.com')
            ->subject('Important: Please come to my office right away')
            ->attachFromFileystem(__DIR__ . '/Fixtures/attachment.txt')
            ->content('Gotcha!')
            ->make();

        $this->assertContains('michael.scott@dundermifflin.com', $email->envelope->to);
        $this->assertContains('dwight.schrute@dundermifflin.com', $email->envelope->cc);
        $this->assertContains('jim.halpert@dundermifflin.com', $email->envelope->cc);
        $this->assertContains('pam.beesly@dundermifflin.com', $email->envelope->bcc);
        $this->assertSame('Important: Please come to my office right away', $email->envelope->subject);
        $this->assertSame('Gotcha!', $email->content);
        $this->assertCount(1, $email->attachments);

        $attachment = $email->content->attachments[0];

        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertSame('attachment.txt', $attachment->name);
        $this->assertSame('text/plain', $attachment->contentType);
    }
}
