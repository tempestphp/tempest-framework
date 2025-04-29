<?php

namespace Tests\Tempest\Fixtures\Requests;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class FormRequestA implements Request
{
    use IsRequest;

    public string $name;

    public FormRequestB $b;
}
