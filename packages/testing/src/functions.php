<?php

namespace Tempest\Testing
{
    function test(mixed $subject = null): Tester
    {
        return new Tester($subject);
    }
}