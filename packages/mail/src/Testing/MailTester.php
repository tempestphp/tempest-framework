<?php

namespace Tempest\Mail\Testing;

use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Mime\Address as SymfonyAddress;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mime\Part\DataPart;
use Tempest\Mail\Attachment;
use Tempest\Mail\Email;
use Tempest\Mail\EmailAddress;
use Tempest\Mail\EmailPriority;
use Tempest\Mail\EmailToSymfonyEmailMapper;
use Tempest\Support\Arr;

use function Tempest\Mapper\map;
use function Tempest\Support\arr;

final class MailTester
{
    private ?Email $sentEmail = null;
    private ?SymfonyEmail $sentSymfonyEmail = null;

    public function __construct(
        private readonly TestingMailer $mailer,
    ) {}

    public function send(Email $email): self
    {
        $this->mailer->send($email);

        $this->sentEmail = $email;
        $this->sentSymfonyEmail = map($email)->with(EmailToSymfonyEmailMapper::class)->do();

        return $this;
    }

    /**
     * Asserts that the given email class was sent.
     *
     * @param class-string<Email> $email
     */
    public function assertSent(string $email, ?Closure $callback = null): self
    {
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
        $this->assertClassStringIsEmail($email);

        Assert::assertFalse(
            condition: (bool) Arr\first($this->mailer->sent, filter: fn (Email $sent) => $sent instanceof $email),
            message: sprintf('Email `%s` was unexpectedly sent.', $email),
        );

        return $this;
    }

    public array $headers {
        get => $this->sentSymfonyEmail->getHeaders()->toArray();
    }

    public array $from {
        get => Arr\map(
            array: $this->sentSymfonyEmail->getFrom(),
            map: fn (SymfonyAddress $address) => new EmailAddress($address->getAddress(), $address->getName()),
        );
    }

    public array $to {
        get => Arr\map(
            array: $this->sentSymfonyEmail->getTo(),
            map: fn (SymfonyAddress $address) => new EmailAddress($address->getAddress(), $address->getName()),
        );
    }

    public array $attachments {
        get => Arr\map(
            array: $this->sentSymfonyEmail->getAttachments(),
            map: fn (DataPart $attachment) => new Attachment(
                resolve: fn () => $attachment->getBody(),
                name: $attachment->getFilename(),
                contentType: $attachment->getMediaType() . '/' . $attachment->getMediaSubtype(),
            ),
        );
    }

    public string $raw {
        get => $this->sentSymfonyEmail->getBody()->bodyToString();
    }

    public string $id {
        get => $this->sentSymfonyEmail->generateMessageId();
    }

    /**
     * Asserts that the email subject contains the given string.
     */
    public function assertSubjectContains(string $expect): self
    {
        Assert::assertStringContainsString(
            needle: $expect,
            haystack: $this->sentSymfonyEmail->getSubject() ?? '',
            message: "Failed asserting that the email's subject is `{$expect}`.",
        );

        return $this;
    }

    /**
     * Asserts that the email was sent to the given addresses.
     */
    public function assertSentTo(string|array $addresses): self
    {
        return $this->assertAddressListContains(
            haystack: $this->sentSymfonyEmail->getTo(),
            needles: $addresses,
            message: 'Failed asserting that the email was sent to [%s]. The recipients are [%s].',
        );
    }

    /**
     * Asserts that the email was not sent to the given addresses.
     */
    public function assertNotSentTo(string|array $addresses): self
    {
        return $this->assertAddressListDoesNotContain(
            haystack: $this->sentSymfonyEmail->getTo(),
            needles: $addresses,
            message: 'Failed asserting that the email was not sent to [%s]. The recipients are [%s].',
        );
    }

    /**
     * Asserts that the email was sent from the given address.
     */
    public function assertFrom(string|array $addresses): self
    {
        return $this->assertAddressListContains(
            haystack: $this->sentSymfonyEmail->getFrom(),
            needles: $addresses,
            message: 'Failed asserting that the email was sent from [%s]. The senders are [%s].',
        );
    }

    /**
     * Asserts that the email was not sent from the given address.
     */
    public function assertNotFrom(string|array $addresses): self
    {
        return $this->assertAddressListDoesNotContain(
            haystack: $this->sentSymfonyEmail->getFrom(),
            needles: $addresses,
            message: 'Failed asserting that the email was not sent from [%s]. The senders are [%s].',
        );
    }

    /**
     * Asserts that the given address was included as a carbon copy.
     */
    public function assertCarbonCopy(string|array $addresses): self
    {
        return $this->assertAddressListContains(
            haystack: $this->sentSymfonyEmail->getCc(),
            needles: $addresses,
            message: 'Failed asserting that [%s] were included in carbon copies. The carbon copy recipients are [%s].',
        );
    }

    /**
     * Asserts that the given address was not included as a carbon copy.
     */
    public function assertNotCarbonCopy(string|array $addresses): self
    {
        return $this->assertAddressListDoesNotContain(
            haystack: $this->sentSymfonyEmail->getCc(),
            needles: $addresses,
            message: 'Failed asserting that [%s] were not included in carbon copies. The carbonm copy recipients are [%s].',
        );
    }

    /**
     * Asserts that the given address was included as a blind carbon copy.
     */
    public function assertBlindCarbonCopy(string|array $addresses): self
    {
        return $this->assertAddressListContains(
            haystack: $this->sentSymfonyEmail->getBcc(),
            needles: $addresses,
            message: 'Failed asserting that [%s] were included in blind carbon copies. The blind carbon copy recipients are [%s].',
        );
    }

    /**
     * Asserts that the given address was not included as a blind carbon copy.
     */
    public function assertNotBlindCarbonCopy(string|array $addresses): self
    {
        return $this->assertAddressListDoesNotContain(
            haystack: $this->sentSymfonyEmail->getBcc(),
            needles: $addresses,
            message: 'Failed asserting that [%s] were not included in blind carbon copies. The blind carbon copy recipients are [%s].',
        );
    }

    /**
     * Asserts that the email has the given priority.
     */
    public function assertPriority(null|int|EmailPriority $priority): self
    {
        if ($priority instanceof EmailPriority) {
            $priority = $priority->value;
        }

        Assert::assertSame(
            expected: $priority,
            actual: $this->sentSymfonyEmail->getPriority(),
            message: 'Failed asserting that the email has a priority of [%s]. The priority is [%s].',
        );

        return $this;
    }

    /**
     * Asserts that the email contains the given string.
     */
    public function assertSee(string $expect): self
    {
        Assert::assertStringContainsString(
            needle: $expect,
            haystack: $this->raw,
            message: "Failed asserting that the email contains `{$expect}`.",
        );

        return $this;
    }

    /**
     * Asserts that the email does not contain the given string.
     */
    public function assertNotSee(string $expect): self
    {
        Assert::assertStringNotContainsString(
            needle: $expect,
            haystack: $this->raw,
            message: "Failed asserting that the email does not contain `{$expect}`.",
        );

        return $this;
    }

    /**
     * Asserts that the email's HTML contains the given string.
     */
    public function assertSeeInHtml(string $expect): self
    {
        Assert::assertNotNull(
            actual: $this->sentSymfonyEmail->getHtmlBody(),
            message: 'The email does not contain an HTML body.',
        );

        Assert::assertStringContainsString(
            needle: $expect,
            haystack: $this->sentSymfonyEmail->getHtmlBody(),
            message: "Failed asserting that the email's HTML contains `{$expect}`.",
        );

        return $this;
    }

    /**
     * Asserts that the email's HTML does not contain the given string.
     */
    public function assertNotSeeInHtml(string $expect): self
    {
        if ($this->sentSymfonyEmail->getHtmlBody() === null) {
            Assert::assertNull($this->sentSymfonyEmail->getHtmlBody());

            return $this;
        }

        Assert::assertStringNotContainsString(
            needle: $expect,
            haystack: $this->sentSymfonyEmail->getHtmlBody(),
            message: "Failed asserting that the email's HTML does not contain `{$expect}`.",
        );

        return $this;
    }

    /**
     * Asserts that the email's text contains the given string.
     */
    public function assertSeeInText(string $expect): self
    {
        Assert::assertNotNull(
            actual: $this->sentSymfonyEmail->getTextBody(),
            message: 'The email does not contain a text body.',
        );

        Assert::assertStringContainsString(
            needle: $expect,
            haystack: $this->sentSymfonyEmail->getTextBody(),
            message: "Failed asserting that the email's text contains `{$expect}`.",
        );

        return $this;
    }

    /**
     * Asserts that the email's text does not contain the given string.
     */
    public function assertNotSeeInText(string $expect): self
    {
        if ($this->sentSymfonyEmail->getTextBody() === null) {
            Assert::assertNull($this->sentSymfonyEmail->getTextBody());

            return $this;
        }

        Assert::assertStringNotContainsString(
            needle: $expect,
            haystack: $this->sentSymfonyEmail->getTextBody(),
            message: "Failed asserting that the email's text does not contain `{$expect}`.",
        );

        return $this;
    }

    /**
     * Asserts that the email has an attachment with the given filename.
     */
    public function assertAttached(string $filename, ?Closure $callback = null): self
    {
        $attachments = $this->sentSymfonyEmail->getAttachments();

        Assert::assertNotEmpty(
            actual: $attachments,
            message: 'Failed asserting that the email has attachments.',
        );

        foreach ($attachments as $attachment) {
            if ($attachment->getFilename() === $filename) {
                if ($callback && $callback(new AttachmentTester($attachment)) === false) {
                    Assert::fail(sprintf('The assertion callback returned `false` for attachment `%s`.', $filename));
                }

                return $this;
            }
        }

        Assert::fail(sprintf(
            'Failed asserting that the email has an attachment named `%s`. Existing attachments: %s.',
            $filename,
            Arr\join(Arr\map($attachments, fn (DataPart $attachment) => $attachment->getName())),
        ));

        return $this;
    }

    /**
     * Asserts that the email has a header with the given name.
     */
    public function assertHasHeader(string $header, ?string $value = null): self
    {
        $headers = Arr\to_array($this->sentSymfonyEmail->getHeaders()->all());

        Assert::assertArrayHasKey(
            key: mb_strtolower($header),
            array: $headers,
            message: sprintf('Failed asserting that the email has a header `%s`.', $header),
        );

        if ($value !== null) {
            Assert::assertSame(
                expected: $value,
                actual: $headers[mb_strtolower($header)]->getBodyAsString(),
                message: sprintf('Failed asserting that the email has a header `%s` with value `%s`.', $header, $value),
            );
        }

        return $this;
    }

    private function assertAddressListContains(null|string|array|EmailAddress $haystack, string|array $needles, string $message): self
    {
        $needles = Arr\wrap($needles);
        $haystack = $this->convertAddresses($haystack);

        foreach ($needles as $address) {
            Assert::assertContains(
                needle: $address,
                haystack: $haystack,
                message: sprintf($message, Arr\join($needles), Arr\join($haystack)),
            );
        }

        return $this;
    }

    private function assertAddressListDoesNotContain(null|string|array|EmailAddress $haystack, string|array $needles, string $message): self
    {
        $needles = Arr\wrap($needles);
        $haystack = $this->convertAddresses($haystack);

        foreach ($needles as $address) {
            Assert::assertNotContains(
                needle: $address,
                haystack: $haystack,
                message: sprintf($message, Arr\join($needles), Arr\join($haystack)),
            );
        }

        return $this;
    }

    private function convertAddresses(null|string|array|EmailAddress $addresses): array
    {
        return arr($addresses)
            ->map(function (string|EmailAddress|SymfonyAddress $address) {
                return match (true) {
                    $address instanceof SymfonyAddress => $address->getAddress(),
                    $address instanceof EmailAddress => $address->email,
                    is_string($address) => $address,
                    default => null,
                };
            })
            ->filter()
            ->toArray();
    }

    private function assertClassStringIsEmail(string $email): void
    {
        if (! is_a($email, Email::class, allow_string: true)) {
            throw new InvalidArgumentException(sprintf('The given email class must implement `%s`.', Email::class));
        }
    }
}
