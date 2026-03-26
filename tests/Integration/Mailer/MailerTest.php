<?php

namespace Tests\Tempest\Integration\Mailer;

use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportException;
use Tempest\Mail\Attachment;
use Tempest\Mail\EmailSendingFailed;
use Tempest\Mail\EmailWasSent;
use Tempest\Mail\Exceptions\RecipientWasMissing;
use Tempest\Mail\Exceptions\SenderWasMissing;
use Tempest\Mail\GenericEmail;
use Tempest\Mail\Transports\NullMailerConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mailer\Fixtures\AttachmentEmail;
use Throwable;

final class MailerTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function event(): void
    {
        $this->eventBus->preventEventHandling();

        $this->mailer->send(new GenericEmail(
            subject: 'Hello',
            to: 'jon@doe.co',
            html: 'Hello Jon',
            from: 'no-reply@tempestphp.com',
        ));

        $this->eventBus->assertDispatched(EmailWasSent::class);
    }

    #[Test]
    public function default_sender(): void
    {
        $this->container->config(new NullMailerConfig(
            defaultSender: 'brent@tempestphp.com',
        ));

        $sent = $this->mailer->send(new GenericEmail(
            subject: 'Hello',
            to: 'jon@doe.co',
            html: 'Hello Jon',
        ));

        $this->assertContains('From: brent@tempestphp.com', $sent->headers);
    }

    #[Test]
    public function sending_mail_requires_from(): void
    {
        $this->expectException(SenderWasMissing::class);

        $this->mailer->send(new GenericEmail(
            subject: 'Hello',
            to: 'jon@doe.co',
            html: 'Hello Jon',
        ));
    }

    #[Test]
    public function sending_mail_requires_to(): void
    {
        $this->expectException(RecipientWasMissing::class);

        $this->mailer->send(new GenericEmail(
            subject: 'Hello',
            to: '',
            html: 'Hello Jon',
            from: 'no-reply@tempestphp.com',
        ));
    }

    #[Test]
    public function send_attachment_with_interface(): void
    {
        $storage = $this->storage->fake();
        $storage->write('attachment.txt', 'owo');

        $this->mailer
            ->send(
                new AttachmentEmail(
                    attachments: [
                        Attachment::fromStorage($storage, 'attachment.txt'),
                    ],
                ),
            )
            ->assertAttached('attachment.txt');
    }

    #[Test]
    public function email_sending_failed_throws_default_exception(): void
    {
        $this->mailer->shouldFail();

        $this->expectException(exception: TransportException::class);
        $this->expectExceptionMessage(message: 'Test transport failure');

        $this->mailer->send(email: new GenericEmail(
            subject: 'Hello',
            to: 'jon@doe.co',
            html: 'Hello Jon',
            from: 'no-reply@tempestphp.com',
        ));
    }

    #[Test]
    public function email_sending_failed_throws_custom_exception(): void
    {
        $this->mailer->shouldFail(exception: new RuntimeException(message: 'SMTP connection refused'));

        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'SMTP connection refused');

        $this->mailer->send(email: new GenericEmail(
            subject: 'Hello',
            to: 'jon@doe.co',
            html: 'Hello Jon',
            from: 'no-reply@tempestphp.com',
        ));
    }

    #[Test]
    public function email_sending_failed_event(): void
    {
        $this->eventBus->preventEventHandling();

        $this->mailer->shouldFail();

        try {
            $this->mailer->send(email: new GenericEmail(
                subject: 'Hello',
                to: 'jon@doe.co',
                html: 'Hello Jon',
                from: 'no-reply@tempestphp.com',
            ));
        } catch (TransportException $exception) {
            $this->assertSame(expected: 'Test transport failure', actual: $exception->getMessage());
        }

        $this->eventBus->assertDispatched(event: EmailSendingFailed::class);
        $this->eventBus->assertNotDispatched(event: EmailWasSent::class);

        $this->mailer->assertFailed(
            email: GenericEmail::class,
            callback: function (GenericEmail $email, Throwable $exception): void {
                $this->assertSame(expected: 'Hello', actual: $email->subject);
                $this->assertSame(expected: 'Test transport failure', actual: $exception->getMessage());
            },
        );
    }

    #[Test]
    public function email_sending_failed_assertion_can_match_exception_message(): void
    {
        $this->mailer->shouldFail(exception: new RuntimeException(message: 'SMTP connection refused'));

        try {
            $this->mailer->send(email: new GenericEmail(
                subject: 'Hello',
                to: 'jon@doe.co',
                html: 'Hello Jon',
                from: 'no-reply@tempestphp.com',
            ));
        } catch (RuntimeException) {
            // @mago-expect lint:no-empty-catch-clause
        }

        $this->mailer->assertFailed(
            email: GenericEmail::class,
            exception: 'SMTP connection refused',
        );
    }

    #[Test]
    public function send_attachment(): void
    {
        $this->skipWindows('Flaky behavior in storage component on Windows and it will be too deep a rabbit hole to debug now.');

        $storage = $this->storage->fake();
        $storage->write('attachment.txt', 'owo');

        $this->mailer
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
