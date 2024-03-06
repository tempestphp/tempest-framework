<?php

namespace Tempest\Http\Session;

interface SessionResolver
{
    public function resolve(): SessionId;
}