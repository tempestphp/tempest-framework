<?php

namespace Tempest\Mail;

enum EmailPriority: int
{
    /**
     * Indicates to email services that immediate attention is required.
     */
    case HIGHEST = 1;

    /**
     * Indicates to email services that this email is important, but not critical.
     */
    case HIGH = 2;

    /**
     * The default priority for regular emails.
     */
    case NORMAL = 3;

    /**
     * Indicates to email services that this email is not important and may be read later.
     */
    case LOW = 4;

    /**
     * Indicates to email services that this email is not important.
     */
    case LOWEST = 5;
}
