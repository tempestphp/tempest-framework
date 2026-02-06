<?php

declare(strict_types=1);

namespace Tempest\AI;

enum AIProvider: string
{
    case OPENAI = 'openai';
    case ANTHROPIC = 'anthropic';
}
