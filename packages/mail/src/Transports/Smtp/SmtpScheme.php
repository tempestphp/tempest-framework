<?php

namespace Tempest\Mail\Transports\Smtp;

enum SmtpScheme: string
{
    case SMTP = 'smtp';
    case SMTPS = 'smtps';
}
