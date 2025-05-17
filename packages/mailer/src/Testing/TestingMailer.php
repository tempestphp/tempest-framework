<?php

namespace Tempest\Mail\Testing;

use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Tempest\Mail\Email;
use Tempest\Mail\EmailToSymfonyEmailMapper;
use Tempest\Mail\Mailer;
use Tempest\Mail\MailerConfig;
use Tempest\Support\Arr;
use Tempest\Support\Random;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

use function Tempest\map;

final class TestingMailer implements Mailer
{
    public function __construct(
        public readonly ?string $tag,
        private readonly MailerConfig $mailerConfig,
        private readonly ViewRenderer $viewRenderer,
    ) {}

    /**
     * List of emails that would have been sent.
     *
     * @var array<Email>
     */
    private(set) array $sent = [];

    public function send(Email $email): SentTestingEmail
    {
        $this->sent[] = $email;

        $symfonyEmail = map($email)
            ->with(fn (Email $from) => new EmailToSymfonyEmailMapper($this->mailerConfig, $this->viewRenderer)->map($from, null))
            ->do();

        return new SentTestingEmail(
            original: $email,
            symfonyEmail: $symfonyEmail,
        );
    }

    /**
     * Asserts that the given email class was sent.
     *
     * @param class-string<Email> $email
     */
    public function assertSent(string $email, ?\Closure $callback = null): self
    {
        $this->assertClassStringIsEmail($email);

        $sentEmail = Arr\first($this->sent, filter: fn (Email $sent) => $sent instanceof $email);

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
        $this->assertClassStringIsEmail($email);

        Assert::assertFalse(
            condition: (bool) Arr\first($this->sent, filter: fn (Email $sent) => $sent instanceof $email),
            message: sprintf('Email `%s` was unexpectedly sent.', $email),
        );

        return $this;
    }

    private function assertClassStringIsEmail(string $email): void
    {
        if (! is_a($email, Email::class, allow_string: true)) {
            throw new InvalidArgumentException(sprintf('The given email class must implement `%s`.', Email::class));
        }
    }
}
