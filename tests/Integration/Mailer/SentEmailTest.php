<?php

namespace Tests\Tempest\Integration\Mailer;

use Tempest\Mail\Attachment;
use Tempest\Mail\EmailAddress;
use Tempest\Mail\EmailPriority;
use Tempest\Mail\GenericEmail;
use Tempest\Mail\MailerConfig;
use Tempest\Mail\Testing\AttachmentTester;
use Tempest\Mail\Testing\MailTester;
use Tempest\View\GenericView;
use Tempest\View\View;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mailer\Fixtures\SendWelcomeEmail;

use function Tempest\View\view;

final class SentEmailTest extends FrameworkIntegrationTestCase
{
    public function test_sent_email_assertions(): void
    {
        $this->mailer
            ->send(new GenericEmail(
                subject: 'Hello',
                to: 'jon@doe.co',
                html: new GenericView('Hello Jon'),
                text: 'Hello Jan',
                from: 'no-reply@tempestphp.com',
                cc: ['cc1@doe.co', 'cc2@doe.co'],
                bcc: ['bcc1@doe.co', 'bcc2@doe.co'],
                replyTo: null,
                headers: ['X-Foo' => 'bar'],
                priority: EmailPriority::NORMAL,
                attachments: [],
            ))
            ->assertSubjectContains('Hello')
            ->assertSee('Hello Jon')
            ->assertNotSee('this is not in the email')
            ->assertSeeInHtml('Hello Jon')
            ->assertNotSeeInText('Hello Jon')
            ->assertSeeInText('Hello Jan')
            ->assertSentTo('jon@doe.co')
            ->assertNotSentTo('imaginary-recipient@example.com')
            ->assertCarbonCopy('cc1@doe.co')
            ->assertCarbonCopy(['cc1@doe.co', 'cc2@doe.co'])
            ->assertNotCarbonCopy('cc3@doe.co')
            ->assertBlindCarbonCopy('bcc1@doe.co')
            ->assertBlindCarbonCopy(['bcc1@doe.co', 'bcc2@doe.co'])
            ->assertNotBlindCarbonCopy('bcc3@doe.co')
            ->assertFrom('no-reply@tempestphp.com')
            ->assertNotFrom('imaginary-expeditor@example.com')
            ->assertPriority(EmailPriority::NORMAL)
            ->assertHasHeader('X-Foo')
            ->assertHasHeader('X-Foo', 'bar');
    }

    public function test_with_default_sender(): void
    {
        /** @var \Tempest\Mail\Transports\Smtp\SmtpMailerConfig $mailerConfig */
        $mailerConfig = $this->container->get(MailerConfig::class);
        $mailerConfig->defaultSender = new EmailAddress('brendt@stitcher.io', 'brendt');

        $this->mailer
            ->send(new GenericEmail(subject: 'Test', to: new EmailAddress('brendt+2@stitcher.io'), html: 'Hi'))
            ->assertFrom('brendt@stitcher.io');
    }

    public function test_send_to_address_vo(): void
    {
        $this->sendTestEmail(
            to: [new EmailAddress('recipient1@example.com', 'Jon Doe'), 'recipient2@example.com'],
            from: 'no-reply@tempestphp.com',
        )
            ->assertSentTo('recipient1@example.com')
            ->assertSentTo('recipient2@example.com');
    }

    public function test_send_to_address_with_brackets(): void
    {
        $sent = $this->sendTestEmail(
            to: ['Jon Doe <recipient1@example.com>', 'recipient2@example.com'],
            from: 'no-reply@tempestphp.com',
        );

        $sent->assertSentTo('recipient1@example.com');
        $sent->assertSentTo('recipient2@example.com');
    }

    public function test_assert_sent_to(): void
    {
        $this->sendTestEmail(
            to: ['recipient1@example.com', 'recipient2@example.com'],
            from: 'no-reply@tempestphp.com',
        )
            ->assertSentTo('recipient1@example.com')
            ->assertSentTo('recipient2@example.com')
            ->assertSentTo(['recipient1@example.com', 'recipient2@example.com'])
            ->assertNotSentTo('recipient3@exampe.com')
            ->assertNotSentTo(['recipient3@exampe.com', 'recipient4@example.com']);
    }

    public function test_rendered_html(): void
    {
        $this->sendTestEmail(
            html: view(__DIR__ . '/Fixtures/welcome.view.php', fullName: 'Jon Doe'),
        )->assertSeeInHtml('Welcome Jon Doe');
    }

    public function test_class_based_html(): void
    {
        $this->mailer
            ->send(new SendWelcomeEmail('jon@doe.co', 'Jon Doe'))
            ->assertSeeInHtml('Welcome Jon Doe')
            ->assertSubjectContains('Welcome Jon Doe')
            ->assertSentTo('jon@doe.co');
    }

    public function test_assert_attachment_from_closure(): void
    {
        $this->sendTestEmail(
            text: 'Hello',
            attachments: [
                Attachment::fromClosure(fn () => 'hey', name: 'file.txt', contentType: 'text/plain'),
            ],
        )->assertAttached('file.txt', function (AttachmentTester $attachment): void {
            $attachment->assertNamed('file.txt');
            $attachment->assertNotNamed('foo.txt');
            $attachment->assertType('text');
            $attachment->assertNotType('image');
        });
    }

    public function test_assert_attachment_from_filesystem(): void
    {
        $this->sendTestEmail(
            text: 'Hello',
            attachments: [
                Attachment::fromFilesystem(__FILE__),
            ],
        )->assertAttached('SentEmailTest.php', function (AttachmentTester $attachment): void {
            $attachment->assertNamed('SentEmailTest.php');
            $attachment->assertNotNamed('foo.txt');
            $attachment->assertType('text');
            $attachment->assertNotType('image');
        });
    }

    public function test_assert_attachment_from_storage(): void
    {
        $this->skipWindows('Flaky behavior in storage component on Windows and it will be too deep a rabbit hole to debug now.');

        $storage = $this->storage->fake();
        $storage->write('file.txt', 'owo');

        $this->sendTestEmail(
            text: 'Hello Jon in Text',
            attachments: [
                Attachment::fromStorage($storage, 'file.txt'),
            ],
        )->assertAttached('file.txt', function (AttachmentTester $attachment): void {
            $attachment->assertNamed('file.txt');
            $attachment->assertNotNamed('foo.txt');
            $attachment->assertType('text');
            $attachment->assertNotType('image');
        });
    }

    public function test_assert_attachment_from_named_storage(): void
    {
        $storage = $this->storage->fake('test-disk');
        $storage->write('file.txt', 'owo');

        $this->sendTestEmail(
            text: 'Hello Jon in Text',
            attachments: [
                Attachment::fromStorage($storage, 'file.txt'),
            ],
        )->assertAttached('file.txt');
    }

    public function test_html_path(): void
    {
        $this->mailer->send(new GenericEmail(
            subject: 'Hello there!',
            to: 'brent.roose@gmail.com',
            html: __DIR__ . '/Fixtures/email.view.php',
            from: 'brendt@stitcher.io',
        ))->assertSeeInHtml('Hi');
    }

    private function sendTestEmail(
        ?string $subject = null,
        null|string|array|EmailAddress $to = null,
        null|string|array|EmailAddress $from = null,
        null|string|array|EmailAddress $cc = null,
        null|string|array|EmailAddress $bcc = null,
        null|string|array|EmailAddress $replyTo = null,
        array $headers = [],
        EmailPriority $priority = EmailPriority::NORMAL,
        null|string|View $html = null,
        ?string $text = null,
        array $attachments = [],
    ): MailTester {
        $content = match (true) {
            $html instanceof View => $html,
            $html !== null => <<<HTML_WRAP
                <html>
                    <body>
                        <h1>{$html}</h1>
                    </body>
                </html>
            HTML_WRAP,
            $text !== null => $text,
            default => 'Hello Jon in Text',
        };

        return $this->mailer->send(new GenericEmail(
            subject: $subject ?? 'Hello',
            to: $to ?? 'jon@doe.co',
            html: $content,
            from: $from ?? 'no-reply@tempestphp.com',
            cc: $cc ?? ['cc1@doe.co', 'cc2@doe.co'],
            bcc: $bcc ?? ['bcc1@doe.co', 'bcc2@doe.co'],
            replyTo: $replyTo,
            headers: $headers ?: ['X-Foo' => 'bar'],
            priority: $priority,
            attachments: $attachments,
        ));
    }
}
