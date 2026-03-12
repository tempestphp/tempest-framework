<?php

namespace Tests\Tempest\Integration\Mailer;

use InvalidArgumentException;
use PHPUnit\Framework\AssertionFailedError;
use Tempest\Mail\Attachment;
use Tempest\Mail\Email;
use Tempest\Mail\EmailWasSent;
use Tempest\Mail\GenericEmail;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mailer\Fixtures\SendWelcomeEmail;
use Tests\Tempest\Integration\Mailer\Fixtures\TextEmail;

final class MailerTesterTest extends FrameworkIntegrationTestCase
{
    public function test_assert_sent_must_have_valid_class_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->mailer->assertSent('foo'); // @phpstan-ignore argument.type
    }

    public function test_assert_sent_must_have_class_string_that_implements_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The given email class must implement `Tempest\Mail\Email`.");

        $this->mailer->assertSent(self::class); // @phpstan-ignore argument.type
    }

    public function test_assert_sent_with_class_string(): void
    {
        $this->mailer->send(new TextEmail())->assertSent(TextEmail::class);
    }

    public function test_assert_sent_with_class_string_and_callback(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Email `Tests\Tempest\Integration\Mailer\Fixtures\TextEmail` was sent but failed the assertion.");

        $this->mailer->send(new TextEmail())->assertSent(TextEmail::class, fn (Email $_email) => false);
    }

    public function test_assert_sent_with_class_string_and_truthy_callback(): void
    {
        $this->mailer->send(new TextEmail())->assertSent(TextEmail::class, fn (Email $_email) => true);
    }

    public function test_assert_not_sent_with_class_string(): void
    {
        $this->mailer->assertNotSent(TextEmail::class);
    }

    public function test_assert_sent_and_not_sent_with_another_class(): void
    {
        $this->mailer
            ->send(new SendWelcomeEmail(
                address: 'jon@doe.co',
                fullName: 'Jon Doe',
            ))
            ->assertSent(SendWelcomeEmail::class)
            ->assertNotSent(TextEmail::class);
    }

    public function test_assertions(): void
    {
        $this->mailer
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

    public function test_email_was_sent_event_was_dispatched(): void
    {
        $this->eventBus->preventEventHandling();

        $this->mailer
            ->send(email: new GenericEmail(
                subject: 'Hello',
                to: 'jon@doe.co',
                html: 'Hello Jon',
                from: 'no-reply@tempestphp.com',
                attachments: [
                    Attachment::fromClosure(callable: fn () => 'hello!'),
                ],
            ));

        $this->eventBus->assertDispatched(event: EmailWasSent::class);
    }
}
