<?php

declare(strict_types=1);

namespace Tempest\AI;

enum MessageRole: string
{
    case SYSTEM = 'system';
    case USER = 'user';
    case ASSISTANT = 'assistant';
}
