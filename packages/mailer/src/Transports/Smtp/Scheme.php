<?php

namespace Tempest\Mail\Transports\Smtp;

enum Scheme: string
{
    case SMTP = 'smtp';
    case SMTPS = 'smtps';
}
