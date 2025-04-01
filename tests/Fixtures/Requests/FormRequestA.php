<?php

namespace Tests\Tempest\Fixtures\Requests;

use Tempest\Router\IsRequest;
use Tempest\Router\Request;

final class FormRequestA implements Request
{
    use IsRequest;

    public string $name;

    public FormRequestB $b;
}
