<?php

namespace Tests\Tempest\Integration\Mailer;

use Tempest\Mail\Attachment;
use Tempest\Mail\EmailWasSent;
use Tempest\Mail\Exceptions\RecipientWasMissing;
use Tempest\Mail\Exceptions\SenderWasMissing;
use Tempest\Mail\GenericEmail;
use Tempest\Mail\Transports\NullMailerConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mailer\Fixtures\AttachmentEmail;

final class MailerTest extends FrameworkIntegrationTestCase
{
    public function test_event(): void
    {
        $this->eventBus->preventEventHandling();

        $this->mail->send(new GenericEmail(
            subject: 'Hello',
            to: 'jon@doe.co',
            html: 'Hello Jon',
            from: 'no-reply@tempestphp.com',
        ));

        $this->eventBus->assertDispatched(EmailWasSent::class);
    }

    public function test_default_sender(): void
    {
        $this->container->config(new NullMailerConfig(
            defaultSender: 'brent@tempestphp.com',
        ));

        $sent = $this->mail->send(new GenericEmail(
            subject: 'Hello',
            to: 'jon@doe.co',
            html: 'Hello Jon',
        ));

        $this->assertContains('From: brent@tempestphp.com', $sent->headers);
    }

    public function test_sending_mail_requires_from(): void
    {
        $this->expectException(SenderWasMissing::class);

        $this->mail->send(new GenericEmail(
            subject: 'Hello',
            to: 'jon@doe.co',
            html: 'Hello Jon',
        ));
    }

    public function test_sending_mail_requires_to(): void
    {
        $this->expectException(RecipientWasMissing::class);

        $this->mail->send(new GenericEmail(
            subject: 'Hello',
            to: '',
            html: 'Hello Jon',
            from: 'no-reply@tempestphp.com',
        ));
    }

    public function test_send_attachment_with_interface(): void
    {
        $storage = $this->storage->fake();
        $storage->write('attachment.txt', 'owo');

        $this->mail
            ->send(
                new AttachmentEmail(
                    attachments: [
                        Attachment::fromStorage($storage, 'attachment.txt'),
                    ],
                ),
            )
            ->assertAttached('attachment.txt');
    }

    public function test_send_attachment(): void
    {
        $storage = $this->storage->fake();
        $storage->write('attachment.txt', 'owo');

        $this->mail
            ->send(new GenericEmail(
                subject: 'Hello',
                to: 'jon@doe.co',
                html: 'Hello Jon',
                from: 'no-reply@tempestphp.com',
                attachments: [
                    Attachment::fromStorage($storage, 'attachment.txt'),
                ],
            ))
            ->assertAttached('attachment.txt');
    }
}
