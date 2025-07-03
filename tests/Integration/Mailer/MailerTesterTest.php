<?php

namespace Tests\Tempest\Integration\Mailer;

use InvalidArgumentException;
use PHPUnit\Framework\AssertionFailedError;
use Tempest\Mail\Attachment;
use Tempest\Mail\Email;
use Tempest\Mail\GenericEmail;
use Tempest\Mail\GenericMailer;
use Tempest\Mail\Mailer;
use Tempest\Mail\Testing\TestingMailer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mailer\Fixtures\SendWelcomeEmail;
use Tests\Tempest\Integration\Mailer\Fixtures\TextOnlyEmail;

final class MailerTesterTest extends FrameworkIntegrationTestCase
{
    private Mailer $mailer {
        get => $this->container->get(Mailer::class);
    }

    public function test_sending_mail_is_prevented_by_default(): void
    {
        $this->assertInstanceOf(TestingMailer::class, $this->mailer);
    }

    public function test_can_allow_sending_actual_emails(): void
    {
        $this->mail->allowSendingEmails();

        $this->assertInstanceOf(GenericMailer::class, $this->mailer);
    }

    public function test_assert_sent_must_have_valid_class_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->mail->assertSent('foo'); // @phpstan-ignore argument.type
    }

    public function test_assert_sent_must_have_class_string_that_implements_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The given email class must implement `Tempest\Mail\Email`.");

        $this->mail->assertSent(self::class); // @phpstan-ignore argument.type
    }

    public function test_assert_sent_with_class_string(): void
    {
        $this->mailer->send(new TextOnlyEmail());
        $this->mail->assertSent(TextOnlyEmail::class);
    }

    public function test_assert_sent_with_class_string_and_callback(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Email `Tests\Tempest\Integration\Mailer\Fixtures\TextOnlyEmail` was sent but failed the assertion.");

        $this->mailer->send(new TextOnlyEmail());
        $this->mail->assertSent(TextOnlyEmail::class, fn (Email $_email) => false);
    }

    public function test_assert_sent_with_class_string_and_truthy_callback(): void
    {
        $this->mailer->send(new TextOnlyEmail());
        $this->mail->assertSent(TextOnlyEmail::class, fn (Email $_email) => true);
    }

    public function test_assert_not_sent_with_class_string(): void
    {
        $this->mail->assertNotSent(TextOnlyEmail::class);
    }

    public function test_assert_sent_and_not_sent_with_another_class(): void
    {
        $this->mailer->send(new SendWelcomeEmail(
            address: 'jon@doe.co',
            fullName: 'Jon Doe',
        ));

        $this->mail->assertSent(SendWelcomeEmail::class);
        $this->mail->assertNotSent(TextOnlyEmail::class);
    }

    public function test_assertions(): void
    {
        $this->mailer->send(new GenericEmail(
            subject: 'Hello',
            to: 'jon@doe.co',
            from: 'no-reply@tempestphp.com',
            text: 'Hello Jon',
            attachments: [
                Attachment::fromClosure(fn () => 'hello!'),
            ],
        ));

        $this->mail->assertSent(GenericEmail::class, function (Email $email): void {
            $this->assertCount(1, $email->content->attachments);
            $this->assertSame('hello!', ($email->content->attachments[0]->resolve)());
        });
    }
}
