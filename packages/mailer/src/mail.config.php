<?php

use Tempest\Mail\Transports\Smtp\SmtpMailerConfig;
use Tempest\Mail\Transports\Smtp\SmtpScheme;

return new SmtpMailerConfig(
    scheme: match (strtolower(env('MAILER_SMTP_SCHEME', default: 'smtp'))) {
        'smtps' => SmtpScheme::SMTPS,
        'smtp' => SmtpScheme::SMTP,
    },
    host: env('MAILER_SMTP_HOST', default: '127.0.0.0'),
    port: env('MAILER_SMTP_PORT', default: 2525),
    username: env('MAILER_SMTP_USERNAME', default: ''),
    password: env('MAILER_SMTP_PASSWORD', default: ''),
);
