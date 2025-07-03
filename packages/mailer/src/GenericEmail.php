<?php

namespace Tempest\Mail;

/**
 * Represents a generic email.
 */
final class GenericEmail implements Email
{
    public function __construct(
        public Envelope $envelope,
        public Content $content,
    ) {}

    /**
     * Builds a new generic email.
     */
    public static function build(): EmailBuilder
    {
        return new EmailBuilder();
    }
}
