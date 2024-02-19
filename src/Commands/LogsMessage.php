<?php

declare(strict_types=1);

namespace Tempest\Commands;

interface LogsMessage
{
    public function getMessage(): ?string;
}
