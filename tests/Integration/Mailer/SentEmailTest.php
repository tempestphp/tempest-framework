<?php

namespace Tests\Tempest\Integration\Mailer;

use Tempest\Mail\Address;
use Tempest\Mail\Attachment;
use Tempest\Mail\Content;
use Tempest\Mail\EmailPriority;
use Tempest\Mail\Envelope;
use Tempest\Mail\GenericEmail;
use Tempest\Mail\Mailer;
use Tempest\Mail\Testing\SentTestingEmail;
use Tempest\Mail\Testing\TestingAttachment;
use Tempest\Mail\Testing\TestingMailer;
use Tempest\View\View;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mailer\Fixtures\SendWelcomeEmail;

use function Tempest\view;

final class SentEmailTest extends FrameworkIntegrationTestCase
{
    private TestingMailer $mailer {
        get => $this->mail->mailer;
    }

    public function test_sent_email_assertions(): void
    {
        $sent = $this->sendTestEmail();

        $sent->assertSubjectContains('Hello');

        $sent->assertSee('Hello Jon');
        $sent->assertNotSee('this is not in the email');

        $sent->assertSeeInHtml('Hello Jon in HTML');
        $sent->assertNotSeeInHtml('this is not in the HTML');

        $sent->assertSeeInText('Hello Jon in Text');
        $sent->assertNotSeeInText('this is not in the Text');

        $sent->assertSentTo('jon@doe.co');
        $sent->assertNotSentTo('imaginary-recipient@example.com');

        $sent->assertCarbonCopy('cc1@doe.co');
        $sent->assertCarbonCopy(['cc1@doe.co', 'cc2@doe.co']);
        $sent->assertNotCarbonCopy('cc3@doe.co');

        $sent->assertBlindCarbonCopy('bcc1@doe.co');
        $sent->assertBlindCarbonCopy(['bcc1@doe.co', 'bcc2@doe.co']);
        $sent->assertNotBlindCarbonCopy('bcc3@doe.co');

        $sent->assertFrom('no-reply@tempestphp.com');
        $sent->assertNotFrom('imaginary-expeditor@example.com');

        $sent->assertPriority(EmailPriority::NORMAL);

        $sent->assertHasHeader('X-Foo');
        $sent->assertHasHeader('X-Foo', 'bar');
    }

    public function test_send_to_address_vo(): void
    {
        $sent = $this->sendTestEmail(
            subject: null,
            to: [new Address('recipient1@example.com', 'Jon Doe'), 'recipient2@example.com'],
            from: 'no-reply@tempestphp.com',
        );

        $sent->assertSentTo('recipient1@example.com');
        $sent->assertSentTo('recipient2@example.com');
    }

    public function test_send_to_address_with_brackets(): void
    {
        $sent = $this->sendTestEmail(
            subject: null,
            to: ['Jon Doe <recipient1@example.com>', 'recipient2@example.com'],
            from: 'no-reply@tempestphp.com',
        );

        $sent->assertSentTo('recipient1@example.com');
        $sent->assertSentTo('recipient2@example.com');
    }

    public function test_assert_sent_to(): void
    {
        $sent = $this->sendTestEmail(
            subject: null,
            to: ['recipient1@example.com', 'recipient2@example.com'],
            from: 'no-reply@tempestphp.com',
        );

        $sent->assertSentTo('recipient1@example.com');
        $sent->assertSentTo('recipient2@example.com');
        $sent->assertSentTo(['recipient1@example.com', 'recipient2@example.com']);

        $sent->assertNotSentTo('recipient3@exampe.com');
        $sent->assertNotSentTo(['recipient3@exampe.com', 'recipient4@example.com']);
    }

    public function test_rendered_html(): void
    {
        $sent = $this->sendTestEmail(
            html: view(__DIR__ . '/Fixtures/welcome.view.php', fullName: 'Jon Doe'),
        );

        $sent->assertSeeInHtml('Welcome Jon Doe');
    }

    public function test_class_based_html(): void
    {
        $sent = $this->mailer->send(new SendWelcomeEmail('jon@doe.co', 'Jon Doe'));

        $sent->assertSeeInHtml('Welcome Jon Doe');
        $sent->assertSubjectContains('Welcome Jon Doe');
        $sent->assertSentTo('jon@doe.co');
    }

    public function test_assert_attachment_from_closure(): void
    {
        $sent = $this->sendTestEmail(
            text: 'Hello',
            attachments: [
                Attachment::fromClosure(fn () => 'hey', name: 'file.txt', contentType: 'text/plain'),
            ],
        );

        $sent->assertAttached('file.txt', function (TestingAttachment $attachment): void {
            $attachment->assertNamed('file.txt');
            $attachment->assertNotNamed('foo.txt');
            $attachment->assertType('text');
            $attachment->assertNotType('image');
        });
    }

    public function test_assert_attachment_from_filesystem(): void
    {
        $sent = $this->sendTestEmail(
            text: 'Hello',
            attachments: [
                Attachment::fromFilesystem(__FILE__),
            ],
        );

        $sent->assertAttached('SentEmailTest.php', function (TestingAttachment $attachment): void {
            $attachment->assertNamed('SentEmailTest.php');
            $attachment->assertNotNamed('foo.txt');
            $attachment->assertType('text');
            $attachment->assertNotType('image');
        });
    }

    public function test_assert_attachment_from_storage(): void
    {
        $storage = $this->storage->fake();
        $storage->write('file.txt', 'owo');

        $sent = $this->sendTestEmail(
            text: 'Hello Jon in Text',
            attachments: [
                Attachment::fromStorage($storage, 'file.txt'),
            ],
        );

        $sent->assertAttached('file.txt', function (TestingAttachment $attachment): void {
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

        $sent = $this->sendTestEmail(
            text: 'Hello Jon in Text',
            attachments: [
                Attachment::fromStorage($storage, 'file.txt'),
            ],
        );

        $sent->assertAttached('file.txt');
    }

    private function sendTestEmail(
        ?string $subject = null,
        null|string|array|Address $to = null,
        null|string|array|Address $from = null,
        null|string|array|Address $cc = null,
        null|string|array|Address $bcc = null,
        null|string|array|Address $replyTo = null,
        array $headers = [],
        EmailPriority $priority = EmailPriority::NORMAL,
        null|string|View $html = null,
        ?string $text = null,
        array $attachments = [],
    ): SentTestingEmail {
        return $this->mailer->send(new GenericEmail(
            subject: $subject ?? 'Hello',
            to: $to ?? 'jon@doe.co',
            cc: $cc ?? ['cc1@doe.co', 'cc2@doe.co'],
            bcc: $bcc ?? ['bcc1@doe.co', 'bcc2@doe.co'],
            from: $from ?? 'no-reply@tempestphp.com',
            replyTo: $replyTo,
            headers: $headers ?: ['X-Foo' => 'bar'],
            priority: $priority,
            text: $text ?? 'Hello Jon in Text',
            html: $html ?? <<<HTML_WRAP
                <html>
                    <body>
                        <h1>Hello Jon in HTML</h1>
                    </body>
                </html>
            HTML_WRAP,
            attachments: $attachments,
        ));
    }
}
