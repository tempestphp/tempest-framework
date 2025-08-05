<?php

namespace Tempest\Http;

interface ServerSentEvent
{
    public array $datalines {
        get;
    }
}
