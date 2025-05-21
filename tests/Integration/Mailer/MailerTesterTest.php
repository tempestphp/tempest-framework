<?php

namespace Tests\Tempest\Integration\Mailer;

use InvalidArgumentException;
use PHPUnit\Framework\AssertionFailedError;
use Tempest\Mail\Mailer;
use Tempest\Mail\Testing\TestingMailer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mailer\Fixtures\SendWelcomeEmail;
use Tests\Tempest\Integration\Mailer\Fixtures\TextOnlyEmail;

final class MailerTesterTest extends FrameworkIntegrationTestCase
{
    public function test_fake_mailer_is_registered_in_container(): void
    {
        $faked = $this->mail->fake();
        $actual = $this->container->get(Mailer::class);

        $this->assertInstanceOf(TestingMailer::class, $faked);
        $this->assertInstanceOf(TestingMailer::class, $actual);
        $this->assertSame($faked, $actual);
    }

    public function test_multiple_fake_mailer_are_registered_in_container(): void
    {
        $faked1 = $this->mail->fake('mailer1');
        $faked2 = $this->mail->fake('mailer2');

        $actual1 = $this->container->get(Mailer::class, 'mailer1');
        $actual2 = $this->container->get(Mailer::class, 'mailer2');

        $this->assertInstanceOf(TestingMailer::class, $faked1);
        $this->assertInstanceOf(TestingMailer::class, $actual1);
        $this->assertSame($faked1, $actual1);

        $this->assertInstanceOf(TestingMailer::class, $faked2);
        $this->assertInstanceOf(TestingMailer::class, $actual2);
        $this->assertSame($faked2, $actual2);

        $this->assertNotSame($actual1, $actual2);
    }

    public function test_assert_sent_must_have_valid_class_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $mailer = $this->mail->fake();

        $mailer->assertSent('foo'); // @phpstan-ignore argument.type
    }

    public function test_assert_sent_must_have_class_string_that_implements_email(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $mailer = $this->mail->fake();

        $mailer->assertSent(self::class); // @phpstan-ignore argument.type
    }

    public function test_assert_sent_with_class_string(): void
    {
        $mailer = $this->mail->fake();

        $mailer->send(new TextOnlyEmail());

        $mailer->assertSent(TextOnlyEmail::class);
    }

    public function test_assert_sent_with_class_string_and_callback(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage("Email `Tests\Tempest\Integration\Mailer\Fixtures\TextOnlyEmail` was sent but failed the assertion.");

        $mailer = $this->mail->fake();

        $mailer->send(new TextOnlyEmail());

        $mailer->assertSent(TextOnlyEmail::class, fn () => false);
    }

    public function test_assert_not_sent_with_class_string(): void
    {
        $mailer = $this->mail->fake();
        $mailer->assertNotSent(TextOnlyEmail::class);
    }

    public function test_assert_sent_and_not_sent_with_another_class(): void
    {
        $mailer = $this->mail->fake();

        $mailer->send(new SendWelcomeEmail(
            address: 'jon@doe.co',
            fullName: 'Jon Doe',
        ));

        $mailer->assertSent(SendWelcomeEmail::class);
        $mailer->assertNotSent(TextOnlyEmail::class);
    }
}
