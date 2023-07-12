<?php

namespace Tempest\Interfaces;

use Tempest\Http\Status;

interface Response
{
    public function getStatus(): Status;

    public function getBody(): string;
}
