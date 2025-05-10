<?php

namespace Tempest\Mail\Testing;

use Closure;
use PHPUnit\Framework\Assert;
use Symfony\Component\Mime\Address as SymfonyAddress;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Tempest\Mail\Address;
use Tempest\Mail\Email;
use Tempest\Mail\Priority;
use Tempest\Mail\SentEmail;
use Tempest\Support\Arr;

use function Tempest\Support\arr;

final class SentTestingEmail implements SentEmail
{
    /**
     * Emails are not actually sent, so there is no protocol logs during tests.
     */
    public string $debug {
        get => '';
    }

    public array $headers {
        get => $this->symfonyEmail->getHeaders()->toArray();
    }

    public string $raw {
        get => $this->symfonyEmail->getBody()->bodyToString();
    }

    public string $id {
        get => $this->symfonyEmail->generateMessageId();
    }

    public function __construct(
        private(set) readonly Email $original,
        private(set) readonly SymfonyEmail $symfonyEmail,
    ) {}

    /**
     * Asserts that the email subject contains the given string.
     */
    public function assertSubjectContains(string $expect): self
    {
        Assert::assertStringContainsString(
            needle: $expect,
            haystack: $this->symfonyEmail->getSubject() ?? '',
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
            haystack: $this->symfonyEmail->getTo(),
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
            haystack: $this->symfonyEmail->getTo(),
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
            haystack: $this->symfonyEmail->getFrom(),
            needles: $addresses,
            message: 'Failed asserting that the email was sent from [%s]. The expeditors are [%s].',
        );
    }

    /**
     * Asserts that the email was not sent from the given address.
     */
    public function assertNotFrom(string|array $addresses): self
    {
        return $this->assertAddressListDoesNotContain(
            haystack: $this->symfonyEmail->getFrom(),
            needles: $addresses,
            message: 'Failed asserting that the email was not sent from [%s]. The expeditors are [%s].',
        );
    }

    /**
     * Asserts that the given address was included as a carbon copy.
     */
    public function assertCarbonCopy(string|array $addresses): self
    {
        return $this->assertAddressListContains(
            haystack: $this->symfonyEmail->getCc(),
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
            haystack: $this->symfonyEmail->getCc(),
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
            haystack: $this->symfonyEmail->getBcc(),
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
            haystack: $this->symfonyEmail->getBcc(),
            needles: $addresses,
            message: 'Failed asserting that [%s] were not included in blind carbon copies. The blind carbon copy recipients are [%s].',
        );
    }

    /**
     * Asserts that the email has the given priority.
     */
    public function assertPriority(null|int|Priority $priority): self
    {
        if ($priority instanceof Priority) {
            $priority = $priority->value;
        }

        Assert::assertSame(
            expected: $priority,
            actual: $this->symfonyEmail->getPriority(),
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
        Assert::assertStringContainsString(
            needle: $expect,
            haystack: $this->symfonyEmail->getHtmlBody(),
            message: "Failed asserting that the email's HTML contains `{$expect}`.",
        );

        return $this;
    }

    /**
     * Asserts that the email's HTML does not contain the given string.
     */
    public function assertNotSeeInHtml(string $expect): self
    {
        Assert::assertStringNotContainsString(
            needle: $expect,
            haystack: $this->symfonyEmail->getHtmlBody(),
            message: "Failed asserting that the email's HTML does not contain `{$expect}`.",
        );

        return $this;
    }

    /**
     * Asserts that the email's text contains the given string.
     */
    public function assertSeeInText(string $expect): self
    {
        Assert::assertStringContainsString(
            needle: $expect,
            haystack: $this->symfonyEmail->getTextBody(),
            message: "Failed asserting that the email's text contains `{$expect}`.",
        );

        return $this;
    }

    /**
     * Asserts that the email's text does not contain the given string.
     */
    public function assertNotSeeInText(string $expect): self
    {
        Assert::assertStringNotContainsString(
            needle: $expect,
            haystack: $this->symfonyEmail->getTextBody(),
            message: "Failed asserting that the email's text does not contain `{$expect}`.",
        );

        return $this;
    }

    /**
     * Asserts that the email has an attachment with the given filename.
     */
    public function assertAttached(string $filename, ?\Closure $callback = null): self
    {
        $attachments = $this->symfonyEmail->getAttachments();

        Assert::assertNotEmpty(
            actual: $attachments,
            message: 'Failed asserting that the email has attachments.',
        );

        foreach ($attachments as $attachment) {
            if ($attachment->getFilename() === $filename) {
                if ($callback && $callback(new TestingAttachment($attachment)) === false) {
                    Assert::fail(sprintf('The assertion callback returned `false` for attachment `%s`.', $filename));
                }

                return $this;
            }
        }

        Assert::fail(sprintf('Failed asserting that the email has an attachment named `%s`.', $filename));

        return $this;
    }

    /**
     * Asserts that the email has a header with the given name.
     */
    public function assertHasHeader(string $header, ?string $value = null): self
    {
        $headers = Arr\to_array($this->symfonyEmail->getHeaders()->all());

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

    private function assertAddressListContains(null|string|array|Address $haystack, string|array $needles, string $message): self
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

    private function assertAddressListDoesNotContain(null|string|array|Address $haystack, string|array $needles, string $message): self
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

    private function convertAddresses(null|string|array|Address $addresses): array
    {
        return arr($addresses)
            ->map(function (string|Address|SymfonyAddress $address) {
                return match (true) {
                    $address instanceof SymfonyAddress => $address->getAddress(),
                    $address instanceof Address => $address->email,
                    is_string($address) => $address,
                    default => null,
                };
            })
            ->filter()
            ->toArray();
    }
}
