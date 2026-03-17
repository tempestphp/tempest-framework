<?php

use Tempest\Mail\EmailAddress;
use Tempest\Mail\Transports\Smtp\SmtpMailerConfig;
use Tempest\Mail\Transports\Smtp\SmtpScheme;

use function Tempest\env;

$defaultSender = null;

if (env('MAIL_SENDER_NAME') && env('MAIL_SENDER_EMAIL')) {
    $defaultSender = new EmailAddress(
        email: env('MAIL_SENDER_EMAIL'),
        name: env('MAIL_SENDER_NAME'),
    );
}

$scheme = strtolower(env('MAIL_SMTP_SCHEME', default: 'smtp'));

return new SmtpMailerConfig(
    scheme: match ($scheme) {
        'smtps' => SmtpScheme::SMTPS,
        'smtp' => SmtpScheme::SMTP,
        default => throw new InvalidArgumentException(sprintf('Unsupported SMTP scheme "%s". Supported schemes: smtp, smtps.', $scheme)),
    },
    host: env('MAIL_SMTP_HOST', default: '127.0.0.1'),
    port: env('MAIL_SMTP_PORT', default: 2525),
    username: env('MAIL_SMTP_USERNAME', default: ''),
    password: env('MAIL_SMTP_PASSWORD', default: ''),
    defaultSender: $defaultSender,
);
