<?php

declare(strict_types=1);

namespace Tempest\Bus;

interface LogsMessage
{
    public function getMessage(): ?string;
}
