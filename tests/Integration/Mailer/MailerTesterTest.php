<?php

namespace Tests\Tempest\Integration\Mailer;

use InvalidArgumentException;
use PHPUnit\Framework\AssertionFailedError;
use Tempest\Mail\Attachment;
use Tempest\Mail\Email;
use Tempest\Mail\GenericEmail;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mailer\Fixtures\SendWelcomeEmail;
use Tests\Tempest\Integration\Mailer\Fixtures\TextEmail;

final class MailerTesterTest extends FrameworkIntegrationTestCase
{
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
        $this->mail->send(new TextEmail())->assertSent(TextEmail::class);
    }

    public function test_assert_sent_with_class_string_and_callback(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Email `Tests\Tempest\Integration\Mailer\Fixtures\TextEmail` was sent but failed the assertion.");

        $this->mail->send(new TextEmail())->assertSent(TextEmail::class, fn (Email $_email) => false);
    }

    public function test_assert_sent_with_class_string_and_truthy_callback(): void
    {
        $this->mail->send(new TextEmail())->assertSent(TextEmail::class, fn (Email $_email) => true);
    }

    public function test_assert_not_sent_with_class_string(): void
    {
        $this->mail->assertNotSent(TextEmail::class);
    }

    public function test_assert_sent_and_not_sent_with_another_class(): void
    {
        $this->mail
            ->send(new SendWelcomeEmail(
                address: 'jon@doe.co',
                fullName: 'Jon Doe',
            ))
            ->assertSent(SendWelcomeEmail::class)
            ->assertNotSent(TextEmail::class);
    }

    public function test_assertions(): void
    {
        $this->mail
            ->send(new GenericEmail(
                subject: 'Hello',
                to: 'jon@doe.co',
                html: 'Hello Jon',
                from: 'no-reply@tempestphp.com',
                attachments: [
                    Attachment::fromClosure(fn () => 'hello!'),
                ],
            ))
            ->assertSent(GenericEmail::class, function (GenericEmail $email): void {
                $this->assertCount(1, $email->attachments);
                $this->assertSame('hello!', ($email->attachments[0]->resolve)());
            });
    }
}
