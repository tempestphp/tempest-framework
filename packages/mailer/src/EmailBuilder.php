<?php

namespace Tempest\Mail;

use Stringable;
use Tempest\Mail\Address;
use Tempest\Mail\Attachment;
use Tempest\Mail\Email;
use Tempest\Mail\EmailPriority;
use Tempest\Mail\GenericEmail;
use Tempest\Storage\Storage;
use Tempest\Support\Arr;
use Tempest\Support\Arr\ArrayInterface;
use Tempest\View\View;

/**
 * A builder class for creating email objects.
 */
final class EmailBuilder
{
    public function __construct(
        private(set) null|string|array|ArrayInterface|Address $to = null,
        private(set) null|string|array|ArrayInterface|Address $from = null,
        private(set) null|string|array|ArrayInterface|Address $replyTo = null,
        private(set) null|string|array|ArrayInterface|Address $cc = null,
        private(set) null|string|array|ArrayInterface|Address $bcc = null,
        private(set) ?string $subject = null,
        private(set) null|string|View $html = null,
        private(set) ?string $text = null,
        private(set) array $attachments = [],
        private(set) EmailPriority|int $priority = EmailPriority::NORMAL,
        private(set) array $headers = [],
    ) {}

    /**
     * Defines the recipients of the email.
     */
    public function to(null|string|array|ArrayInterface|Address $to): self
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Defines the sender of the email.
     */
    public function from(null|string|array|ArrayInterface|Address $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Defines the reply-to address of the email.
     */
    public function replyTo(null|string|array|ArrayInterface|Address $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * Defines the carbon-copy recipients of the email.
     */
    public function cc(null|string|array|ArrayInterface|Address $cc): self
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * Defines the blind carbon-copy recipients of the email.
     */
    public function bcc(null|string|array|ArrayInterface|Address $bcc): self
    {
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * Defines the subject of the email.
     */
    public function subject(string|Stringable $subject): self
    {
        $this->subject = (string) $subject;

        return $this;
    }

    /**
     * Defines the HTML body of the email.
     */
    public function html(string|View $html): self
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Defines the text body of the email.
     */
    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Defines the priority of the email.
     */
    public function priority(EmailPriority|int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Defines the headers of the email.
     */
    public function headers(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Adds attachments to the email.
     */
    public function attach(Attachment ...$attachments): self
    {
        foreach ($attachments as $attachment) {
            if (! ($attachment instanceof Attachment)) {
                throw new \InvalidArgumentException(sprintf('All attachments must be instances of `%s`.', Attachment::class));
            }

            $this->attachments[] = $attachment;
        }

        return $this;
    }

    /**
     * Adds an attachment from the filesystem.
     */
    public function attachFromFileystem(string $path, ?string $name = null, ?string $contentType = null): self
    {
        $this->attachments[] = Attachment::fromFilesystem($path, $name, $contentType);

        return $this;
    }

    /**
     * Adds an attachment from the storage.
     */
    public function attachFromStorage(Storage $storage, string $path, ?string $name = null, ?string $contentType = null): self
    {
        $this->attachments[] = Attachment::fromStorage($storage, $path, $name, $contentType);

        return $this;
    }

    /**
     * Builds the email.
     */
    public function make(): Email
    {
        return new GenericEmail(
            subject: $this->subject,
            to: Arr\wrap($this->to),
            from: Arr\wrap($this->from),
            cc: Arr\wrap($this->cc),
            bcc: Arr\wrap($this->bcc),
            replyTo: Arr\wrap($this->replyTo),
            priority: is_int($this->priority)
                ? EmailPriority::from($this->priority)
                : $this->priority,
            headers: $this->headers,
            html: $this->html,
            text: $this->text,
            attachments: $this->attachments,
        );
    }
}
