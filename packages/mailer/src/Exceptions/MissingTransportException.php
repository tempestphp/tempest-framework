<?php

namespace Tempest\Mail\Exceptions;

use Exception;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesApiAsyncAwsTransport;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesHttpAsyncAwsTransport;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesSmtpTransport;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkApiTransport;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkSmtpTransport;

final class MissingTransportException extends Exception implements MailerException
{
    public function __construct(
        private readonly string $missing,
    ) {
        $packageName = $this->getPackageName();
        $message = $packageName
            ? sprintf('The `%s` transport is missing. Install it using `composer require %s`.', $missing, $packageName)
            : sprintf('The `%s` transport is missing.', $missing);

        parent::__construct($message);
    }

    private function getPackageName(): ?string
    {
        return match ($this->missing) {
            PostmarkApiTransport::class => 'symfony/postmark-mailer',
            PostmarkSmtpTransport::class => 'symfony/postmark-mailer',
            SesApiAsyncAwsTransport::class => 'symfony/amazon-mailer',
            SesHttpAsyncAwsTransport::class => 'symfony/amazon-mailer',
            SesSmtpTransport::class => 'symfony/amazon-mailer',
            default => null,
        };
    }
}
