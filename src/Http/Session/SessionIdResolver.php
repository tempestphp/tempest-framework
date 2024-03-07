<?php

namespace Tempest\Http\Session;

interface SessionIdResolver
{
    public function resolve(): SessionId;
}