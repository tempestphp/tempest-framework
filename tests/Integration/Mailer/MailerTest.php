<?php

namespace Tests\Tempest\Integration\Mailer;

use Tempest\Mail\Content;
use Tempest\Mail\Envelope;
use Tempest\Mail\Exceptions\MissingExpeditorAddressException;
use Tempest\Mail\Exceptions\MissingRecipientAddressException;
use Tempest\Mail\GenericEmail;
use Tempest\Mail\Mailer;
use Tempest\Mail\StorageAttachment;
use Tempest\Mail\Transports\Smtp\Scheme;
use Tempest\Mail\Transports\Smtp\SmtpMailerConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class MailerTest extends FrameworkIntegrationTestCase
{
    public function test_default_mailer_is_resolvable(): void
    {
        $mailer = $this->container->get(Mailer::class);

        $this->assertInstanceOf(Mailer::class, $mailer);
    }

    public function test_sending_mail_requires_from(): void
    {
        $this->expectException(MissingExpeditorAddressException::class);

        $mailer = $this->mail->fake();

        $mailer->send(new GenericEmail(
            envelope: new Envelope(
                subject: 'Hello',
                to: 'jon@doe.co',
            ),
            content: new Content(text: 'Hello Jon'),
        ));
    }

    public function test_sending_mail_requires_to(): void
    {
        $this->expectException(MissingRecipientAddressException::class);

        $mailer = $this->mail->fake();

        $mailer->send(new GenericEmail(
            envelope: new Envelope(
                subject: 'Hello',
                to: '',
                from: 'no-reply@tempestphp.com',
            ),
            content: new Content(text: 'Hello Jon'),
        ));
    }

    // public function test_send_attachment(): void
    // {
    //     $storage = $this->storage->fake();
    //     $storage->write('file.txt', 'owo');
    //     $this->container->config(new SmtpMailerConfig(
    //         scheme: Scheme::SMTP,
    //         host: '127.0.0.1',
    //         port: 2525,
    //         username: 'Owo',
    //         password: null,
    //     ));
    //     $this->container->get(Mailer::class)->send(new GenericEmail(
    //         envelope: new Envelope(
    //             subject: 'Hello',
    //             to: 'jon@doe.co',
    //             from: 'no-reply@tempestphp.com',
    //         ),
    //         content: new Content(
    //             text: 'Hello Jon',
    //             attachments: [
    //                 StorageAttachment::fromPath('file.txt'),
    //             ],
    //         ),
    //     ));
    // }
}
