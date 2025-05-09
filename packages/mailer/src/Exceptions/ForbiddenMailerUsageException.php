<?php

namespace Tempest\Mail\Exceptions;

use Exception;

final class ForbiddenMailerUsageException extends Exception implements MailerException
{
    public function __construct(
        public readonly ?string $tag = null,
    ) {
        parent::__construct(
            message: $tag
                ? "Mailer `{$tag}` is being used without a testing fake."
                : 'Mailer is being used without a testing fake.',
        );
    }
}
