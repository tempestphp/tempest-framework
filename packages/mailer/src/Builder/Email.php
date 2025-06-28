<?php

namespace Tempest\Mail\Builder;

use Stringable;
use Tempest\Mail\Address;
use Tempest\Mail\Attachments\Attachment;
use Tempest\Mail\Attachments\FileAttachment;
use Tempest\Mail\Attachments\StorageAttachment;
use Tempest\Mail\Content;
use Tempest\Mail\Email as EmailInterface;
use Tempest\Mail\Envelope;
use Tempest\Mail\GenericEmail;
use Tempest\Mail\Priority;
use Tempest\Support\Arr;
use Tempest\Support\Arr\ArrayInterface;
use Tempest\View\View;
use UnitEnum;

final class Email
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
        private(set) Priority|int $priority = Priority::NORMAL,
        private(set) array $headers = [],
        private(set) array $attachments = [],
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
    public function withSubject(string|Stringable $subject): self
    {
        $this->subject = (string) $subject;

        return $this;
    }

    /**
     * Defines the HTML body of the email.
     */
    public function withHtml(string|View $html): self
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Defines the text body of the email.
     */
    public function withText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Defines the priority of the email.
     */
    public function withPriority(Priority|int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Defines the headers of the email.
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Defines the attachments of the email.
     *
     * @param Attachment[] $attachments
     */
    public function withAttachments(array $attachments): self
    {
        foreach ($attachments as $attachment) {
            if (! ($attachment instanceof Attachment)) {
                throw new \InvalidArgumentException(sprintf('All attachments must be instances of `%s`.', Attachment::class));
            }
        }

        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Adds an attachment to the email.
     */
    public function withAttachment(Attachment $attachment): self
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * Adds an attachment from the filesystem.
     */
    public function withFileAttachment(string $path, ?string $name = null, ?string $contentType = null): self
    {
        $this->attachments[] = FileAttachment::fromPath($path, $name, $contentType);

        return $this;
    }

    /**
     * Adds an attachment from the storage.
     */
    public function withStorageAttachment(string $path, ?string $name = null, ?string $contentType = null, null|string|UnitEnum $tag = null): self
    {
        $this->attachments[] = StorageAttachment::fromPath($path, $name, $contentType, $tag);

        return $this;
    }

    /**
     * Builds the email.
     */
    public function make(): EmailInterface
    {
        return new GenericEmail(
            envelope: new Envelope(
                subject: $this->subject,
                to: Arr\wrap($this->to),
                from: Arr\wrap($this->from),
                cc: Arr\wrap($this->cc),
                bcc: Arr\wrap($this->bcc),
                replyTo: Arr\wrap($this->replyTo),
                priority: is_int($this->priority)
                    ? Priority::from($this->priority)
                    : $this->priority,
                headers: $this->headers,
            ),
            content: new Content(
                html: $this->html,
                text: $this->text,
                attachments: $this->attachments,
            ),
        );
    }
}
