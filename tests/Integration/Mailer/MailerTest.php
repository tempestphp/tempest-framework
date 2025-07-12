<?php

namespace Tests\Tempest\Integration\Mailer;

use Tempest\EventBus\EventBus;
use Tempest\Mail\Attachment;
use Tempest\Mail\EmailSent;
use Tempest\Mail\Exceptions\RecipientWasMissing;
use Tempest\Mail\Exceptions\SenderWasMissing;
use Tempest\Mail\GenericEmail;
use Tempest\Mail\Mailer;
use Tempest\Mail\Transports\NullMailerConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class MailerTest extends FrameworkIntegrationTestCase
{
    private Mailer $mailer {
        get => $this->container->get(Mailer::class);
    }

    public function test_event(): void
    {
        $sent = false;

        $this->container
            ->get(EventBus::class)
            ->listen(function (EmailSent $event) use (&$sent): void {
                $sent = $event;
            });

        $this->mailer->send(new GenericEmail(
            subject: 'Hello',
            to: 'jon@doe.co',
            from: 'no-reply@tempestphp.com',
            text: 'Hello Jon',
        ));

        $this->assertInstanceOf(EmailSent::class, $sent);
    }

    public function test_default_sender(): void
    {
        $this->mail->allowSendingEmails();

        $this->container->config(new NullMailerConfig(
            defaultSender: 'brent@tempestphp.com',
        ));

        $sent = $this->mailer->send(new GenericEmail(
            subject: 'Hello',
            to: 'jon@doe.co',
            text: 'Hello Jon',
        ));

        $this->assertContains('From: brent@tempestphp.com', $sent->headers);
    }

    public function test_sending_mail_requires_from(): void
    {
        $this->expectException(SenderWasMissing::class);

        $this->mailer->send(new GenericEmail(
            subject: 'Hello',
            to: 'jon@doe.co',
            text: 'Hello Jon',
        ));
    }

    public function test_sending_mail_requires_to(): void
    {
        $this->expectException(RecipientWasMissing::class);

        $this->mailer->send(new GenericEmail(
            subject: 'Hello',
            to: '',
            from: 'no-reply@tempestphp.com',
            text: 'Hello Jon',
        ));
    }

    public function test_send_attachment(): void
    {
        $storage = $this->storage->fake();
        $storage->write('file.txt', 'owo');

        $this->mail->allowSendingEmails();
        $this->container->config(new NullMailerConfig());

        $sent = $this->mailer->send(new GenericEmail(
            subject: 'Hello',
            to: 'jon@doe.co',
            from: 'no-reply@tempestphp.com',
            text: 'Hello Jon',
            attachments: [
                Attachment::fromStorage($storage, 'file.txt'),
            ],
        ));

        $this->assertCount(1, $sent->attachments);
        $this->assertSame('file.txt', $sent->attachments[0]->name);
        $this->assertSame('text/plain', $sent->attachments[0]->contentType);
        $this->assertSame('owo', ($sent->attachments[0]->resolve)());
    }
}
