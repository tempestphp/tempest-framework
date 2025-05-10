<?php

namespace Tests\Tempest\Integration\Mailer;

use Tempest\Mail\Address;
use Tempest\Mail\Content;
use Tempest\Mail\Envelope;
use Tempest\Mail\GenericEmail;
use Tempest\Mail\Priority;
use Tempest\Mail\StorageAttachment;
use Tempest\Mail\Testing\SentTestingEmail;
use Tempest\Mail\Testing\TestingAttachment;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mailer\Fixtures\SendWelcomeEmail;

use function Tempest\view;

final class SentEmailTest extends FrameworkIntegrationTestCase
{
    private function sendTestEmail(?Envelope $envelope = null, ?Content $content = null): SentTestingEmail
    {
        return $this->mail->fake()->send(new GenericEmail(
            envelope: $envelope ?? new Envelope(
                subject: 'Hello',
                to: 'jon@doe.co',
                cc: ['cc1@doe.co', 'cc2@doe.co'],
                bcc: ['bcc1@doe.co', 'bcc2@doe.co'],
                from: 'no-reply@tempestphp.com',
            ),
            content: $content ?? new Content(
                text: 'Hello Jon in Text',
                html: <<<HTML_WRAP
                    <html>
                        <body>
                            <h1>Hello Jon in HTML</h1>
                        </body>
                    </html>
                HTML_WRAP,
            ),
        ));
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

        $sent->assertPriority(Priority::NORMAL);
    }

    public function test_send_to_address_vo(): void
    {
        $sent = $this->sendTestEmail(envelope: new Envelope(
            subject: null,
            to: [new Address('recipient1@example.com', 'Jon Doe'), 'recipient2@example.com'],
            from: 'no-reply@tempestphp.com',
        ));

        $sent->assertSentTo('recipient1@example.com');
        $sent->assertSentTo('recipient2@example.com');
    }

    public function test_send_to_address_with_brackets(): void
    {
        $sent = $this->sendTestEmail(envelope: new Envelope(
            subject: null,
            to: ['Jon Doe <recipient1@example.com>', 'recipient2@example.com'],
            from: 'no-reply@tempestphp.com',
        ));

        $sent->assertSentTo('recipient1@example.com');
        $sent->assertSentTo('recipient2@example.com');
    }

    public function test_assert_sent_to(): void
    {
        $sent = $this->sendTestEmail(envelope: new Envelope(
            subject: null,
            to: ['recipient1@example.com', 'recipient2@example.com'],
            from: 'no-reply@tempestphp.com',
        ));

        $sent->assertSentTo('recipient1@example.com');
        $sent->assertSentTo('recipient2@example.com');
        $sent->assertSentTo(['recipient1@example.com', 'recipient2@example.com']);

        $sent->assertNotSentTo('recipient3@exampe.com');
        $sent->assertNotSentTo(['recipient3@exampe.com', 'recipient4@example.com']);
    }

    public function test_rendered_html(): void
    {
        $sent = $this->sendTestEmail(content: new Content(
            html: view('./Fixtures/welcome.view.php', fullName: 'Jon Doe'),
        ));

        $sent->assertSeeInHtml('Welcome Jon Doe');
    }

    public function test_class_based_html(): void
    {
        $sent = $this->mail
            ->fake()
            ->send(new SendWelcomeEmail('jon@doe.co', 'Jon Doe'));

        $sent->assertSeeInHtml('Welcome Jon Doe');
        $sent->assertSubjectContains('Welcome Jon Doe');
        $sent->assertSentTo('jon@doe.co');
    }

    public function test_assert_attachment_from_storage(): void
    {
        $storage = $this->storage->fake();
        $storage->write('file.txt', 'owo');

        $sent = $this->sendTestEmail(content: new Content(
            text: 'Hello Jon in Text',
            attachments: [
                StorageAttachment::fromPath('file.txt'),
            ],
        ));

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

        $sent = $this->sendTestEmail(content: new Content(
            text: 'Hello Jon in Text',
            attachments: [
                StorageAttachment::fromPath('file.txt', tag: 'test-disk'),
            ],
        ));

        $sent->assertAttached('file.txt');
    }
}
