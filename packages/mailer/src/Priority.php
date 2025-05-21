<?php

namespace Tempest\Mail;

enum Priority: int
{
    /**
     * The highest priority.
     */
    case HIGHEST = 1;

    /**
     * The second highest priority.
     */
    case HIGH = 2;

    /**
     * The default priority.
     */
    case NORMAL = 3;

    /**
     * The second lowest priority.
     */
    case LOW = 4;

    /**
     * The lowest priority.
     */
    case LOWEST = 5;
}
