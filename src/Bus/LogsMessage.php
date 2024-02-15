<?php

namespace Tempest\Bus;

interface LogsMessage
{
    public function getMessage(): ?string;
}
