<?php

namespace Tempest\Mail;

/**
 * Represents a generic, basic email.
 */
final class GenericEmail implements Email
{
    public function __construct(
        public Envelope $envelope,
        public Content $content,
    ) {}
}
