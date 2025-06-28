<?php

namespace Tempest\Mail\Testing;

use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Tempest\Container\Container;
use Tempest\Mail\Email;
use Tempest\Mail\Mailer;
use Tempest\Mail\MailerConfig;
use Tempest\Support\Arr;
use Tempest\View\ViewRenderer;

final class MailerTester
{
    private(set) ?TestingMailer $mailer = null;

    public function __construct(
        private Container $container,
    ) {}

    /**
     * Prevents emails from being actually sent. This is the default behavior during tests.
     */
    public function preventSendingEmails(): void
    {
        $this->mailer ??= new TestingMailer(
            mailerConfig: $this->container->get(MailerConfig::class),
            viewRenderer: $this->container->get(ViewRenderer::class),
        );

        $this->container->singleton(Mailer::class, $this->mailer);
    }

    /**
     * Disables the testing mailer, so emails can actually be sent. This is usually not recommended.
     */
    public function allowSendingEmails(): void
    {
        $this->container->unregister(Mailer::class);
    }

    /**
     * Asserts that the given email class was sent.
     *
     * @param class-string<Email> $email
     */
    public function assertSent(string $email, ?\Closure $callback = null): self
    {
        $this->ensureTestingSetUp();
        $this->assertClassStringIsEmail($email);

        $sentEmail = Arr\first($this->mailer->sent, filter: fn (Email $sent) => $sent instanceof $email);

        Assert::assertTrue(
            condition: (bool) $sentEmail,
            message: sprintf('Email `%s` was not sent.', $email),
        );

        if ($callback) {
            try {
                if ($callback($sentEmail) === false) {
                    throw new ExpectationFailedException('The assertion callback returned `false`.');
                }
            } catch (ExpectationFailedException $previous) {
                throw new ExpectationFailedException(
                    message: sprintf('Email `%s` was sent but failed the assertion.', $email),
                    previous: $previous,
                );
            }
        }

        return $this;
    }

    /**
     * Asserts that the given email class was not sent.
     *
     * @param class-string<Email> $email
     */
    public function assertNotSent(string $email): self
    {
        $this->ensureTestingSetUp();
        $this->assertClassStringIsEmail($email);

        Assert::assertFalse(
            condition: (bool) Arr\first($this->mailer->sent, filter: fn (Email $sent) => $sent instanceof $email),
            message: sprintf('Email `%s` was unexpectedly sent.', $email),
        );

        return $this;
    }

    private function ensureTestingSetUp(): void
    {
        if (is_null($this->mailer)) {
            throw new ExpectationFailedException('Mail testing is not set up. Please call `$this->mailer->preventSendingEmails()` before running assertions.');
        }
    }

    private function assertClassStringIsEmail(string $email): void
    {
        if (! is_a($email, Email::class, allow_string: true)) {
            throw new InvalidArgumentException(sprintf('The given email class must implement `%s`.', Email::class));
        }
    }
}
